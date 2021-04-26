<?php

namespace Actengage\LaravelPassendo\Exceptions;

use Exception;

class InvalidTrackingId extends Exception {
    
    public function __construct()
    {
        parent::__construct('No tracking id is defined.');
    }
}