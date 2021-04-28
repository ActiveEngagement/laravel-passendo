<?php

namespace Actengage\LaravelPassendo\Contracts;

interface TrackPassendoConversions {

    public function cpa(): float;

    public function tid(): ?string;

}