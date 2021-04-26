<?php

namespace Actengage\NightWatch\Tests\Unit;

use Actengage\LaravelPassendo\Jobs\TrackClick;
use Actengage\LaravelPassendo\Click;
use Actengage\LaravelPassendo\Request;
use Actengage\LaravelPassendo\Tests\TestCase;
use Actengage\LaravelPassendo\Tests\User;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Queue;
use Throwable;

class ClickTest extends TestCase {

    public function testThatClicksDispatchEvents()
    {
        Queue::fake();

        Click::create([
            'tracking_id' => '123',
            'cpa' => 1
        ]);
        
        Queue::assertPushed(TrackClick::class);
    }

    public function testThatClicksWithValidTrackingId()
    {
        Request::mock($handler = new MockHandler([
            new Response(500),
            new Response(500),
            new Response(200),
        ]));
        
        $click = Click::create([
            'tracking_id' => '123',
            'cpa' => 1
        ]);        

        $click = $click->fresh();

        $this->assertEquals(1, $click->requests()->count());        
        $this->assertEquals(500, $click->status);
        $this->assertFalse($click->success);
        $this->assertNotNull($click->exception);

        while($handler->count()) {
            try {
                $click->track();
            }
            catch(RequestException $e) {
                $click->failed($e);
            }
        }

        $this->assertEquals(3, $click->requests()->count());
        
        $click = $click->fresh();

        $click->parent()->associate(User::create());

        $this->assertEquals(200, $click->status);
        $this->assertTrue($click->success);
        $this->assertNull($click->exception);
        $this->assertInstanceOf(User::class, $click->parent);
    }

    public function testThatClicksWithInvalidTrackingId()
    {
        $click = new Click([
            'tracking_id' => '@trackingid@',
            'cpa' => 1
        ]);

        $job = new TrackClick($click);
        
        try {
            $job->handle();
        } catch(Throwable $e) {
            $job->failed($e);
        }

        $this->assertEquals(0, $click->code);
        $this->assertFalse($click->success);
        $this->assertNotNull($click->exception);
    }

}