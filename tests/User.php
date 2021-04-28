<?php

namespace Actengage\LaravelPassendo\Tests;

use Actengage\LaravelPassendo\Contracts\TrackPassendoConversions as TrackPassendoConversionsInterface;
use Actengage\LaravelPassendo\TrackPassendoConversions;
use Illuminate\Database\Eloquent\Model;

class User extends Model implements TrackPassendoConversionsInterface {

    use TrackPassendoConversions;

    public function cpa(): float
    {
        return 1;
    }

    public function tid(): string
    {
        return 'test';
    }

}