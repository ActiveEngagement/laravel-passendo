<?php

namespace Actengage\NightWatch\Tests\Unit;

use Actengage\LaravelPassendo\Jobs\TrackConversion;
use Actengage\LaravelPassendo\Conversion;
use Actengage\LaravelPassendo\Request;
use Actengage\LaravelPassendo\Tests\TestCase;
use Actengage\LaravelPassendo\Tests\User;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Queue;
use Throwable;

class ConversionTest extends TestCase {

    public function testThatConversionsDispatchEvents()
    {
        Queue::fake();

        Conversion::create([
            'tracking_id' => '123',
            'cpa' => 1
        ]);
        
        Queue::assertPushed(TrackConversion::class);
    }

    public function testThatConversionsWithValidTrackingId()
    {
        Request::mock($handler = new MockHandler([
            new Response(500),
            new Response(500),
            new Response(200),
        ]));
        
        $Conversion = Conversion::create([
            'tracking_id' => '123',
            'cpa' => 1
        ]);        

        $Conversion = $Conversion->fresh();

        $this->assertEquals(1, $Conversion->requests()->count());        
        $this->assertEquals(500, $Conversion->status);
        $this->assertFalse($Conversion->success);
        $this->assertNotNull($Conversion->exception);

        while($handler->count()) {
            try {
                $Conversion->track();
            }
            catch(RequestException $e) {
                $Conversion->failed($e);
            }
        }

        $this->assertEquals(3, $Conversion->requests()->count());
        
        $Conversion = $Conversion->fresh();

        $Conversion->parent()->associate(User::create());

        $this->assertEquals(200, $Conversion->status);
        $this->assertTrue($Conversion->success);
        $this->assertNull($Conversion->exception);
        $this->assertInstanceOf(User::class, $Conversion->parent);
    }

    public function testThatConversionsWithInvalidTrackingId()
    {
        $Conversion = new Conversion([
            'tracking_id' => '@trackingid@',
            'cpa' => 1
        ]);

        $job = new TrackConversion($Conversion);
        
        try {
            $job->handle();
        } catch(Throwable $e) {
            $job->failed($e);
        }

        $this->assertEquals(0, $Conversion->code);
        $this->assertFalse($Conversion->success);
        $this->assertNotNull($Conversion->exception);
    }

}