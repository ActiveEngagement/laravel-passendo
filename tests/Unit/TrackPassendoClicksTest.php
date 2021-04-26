<?php

namespace Actengage\NightWatch\Tests\Unit;

use Actengage\LaravelPassendo\Request;
use Actengage\LaravelPassendo\Tests\TestCase;
use Actengage\LaravelPassendo\Tests\User;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;

class TrackPassendoClicksTest extends TestCase {

    public function testImplementingTrait()
    {
        Request::mock(new MockHandler([
            new Response(200)
        ]));

        $user = User::create();

        $this->assertInstanceOf(User::class, $user->clicks()->first()->parent);
        $this->assertEquals(1, $user->clicks()->count());
    }

}