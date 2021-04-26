<?php

namespace Actengage\LaravelPassendo\Contracts;

interface TrackPassendoClicks {

    public function cpa(): int;

    public function tid(): string;

}