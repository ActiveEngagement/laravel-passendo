<?php

namespace Actengage\LaravelPassendo\Exceptions;

use Exception;

class MethodNotDefined extends Exception
{    
    public function __construct(string $method)
    {
        parent::__construct('The '.$method.'() method is not defined.');
    }
}