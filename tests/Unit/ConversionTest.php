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
        
        $conversion = Conversion::create([
            'tracking_id' => '123',
            'cpa' => 1
        ]);        

        $conversion = $conversion->fresh();

        $this->assertEquals(1, $conversion->requests()->count());        
        $this->assertEquals(500, $conversion->status);
        $this->assertFalse($conversion->success);
        $this->assertNotNull($conversion->exception);

        while($handler->count()) {
            try {
                $conversion->track();
            }
            catch(RequestException $e) {
                $conversion->failed($e);
            }
        }

        $this->assertEquals(3, $conversion->requests()->count());
        
        $conversion = $conversion->fresh();

        $conversion->parent()->associate(User::create());

        $this->assertEquals(200, $conversion->status);
        $this->assertTrue($conversion->success);
        $this->assertNull($conversion->exception);
        $this->assertInstanceOf(User::class, $conversion->parent);
    }

    public function testThatConversionsWithInvalidTrackingId()
    {
        $conversion = new Conversion([
            'tracking_id' => '@trackingid@',
            'cpa' => 1
        ]);

        $job = new TrackConversion($conversion);
        
        try {
            $job->handle();
        } catch(Throwable $e) {
            $job->failed($e);
        }

        $this->assertEquals(0, $conversion->code);
        $this->assertFalse($conversion->success);
        $this->assertNotNull($conversion->exception);
    }

}