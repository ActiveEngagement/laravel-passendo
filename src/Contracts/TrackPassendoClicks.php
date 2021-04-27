<?php

namespace Actengage\LaravelPassendo\Contracts;

interface TrackPassendoClicks {

    public function cpa(): float;

    public function tid(): string;

    public function shouldTrackPassendoClicks(): bool;

}