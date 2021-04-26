<?php

namespace Actengage\LaravelPassendo\Tests;

use Actengage\LaravelPassendo\Contracts\TrackPassendoClicks as TrackPassendoClicksInterface;
use Actengage\LaravelPassendo\TrackPassendoClicks;
use Illuminate\Database\Eloquent\Model;

class User extends Model implements TrackPassendoClicksInterface {

    use TrackPassendoClicks;

    public function cpa(): int
    {
        return 1;
    }

    public function tid(): string
    {
        return 'test';
    }
}