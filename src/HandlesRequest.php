<?php

namespace Actengage\LaravelPassendo;

use GuzzleHttp\Exception\BadResponseException;
use Psr\Http\Message\ResponseInterface;
use Throwable;

trait HandlesRequest {
    
    /**
     * Set the status attribute.
     * 
     * @param  mixed  $value
     * @return void
     */
    public function setStatusAttribute($value)
    {
        if($value instanceof BadResponseException) {
            $value = $value->getResponse();
        }

        if($value instanceof ResponseInterface) {
            $value = $value->getStatusCode();
        }

        if($value instanceof Throwable) {
            $value = $value->getCode();
        }

        $this->attributes['status'] = (int) $value;

        // Set the success based on the status codes.
        $this->success = $this->isSuccessful();
    }
    
    /**
     * Set the success attribute.
     * 
     * @param  mixed  $value
     * @return void
     */
    public function setSuccessAttribute($value)
    {
        // If the status is set to `true`, then nullify the exception
        if($this->attributes['success'] = (bool) $value) {
            $this->exception = null;
        }
    }
    
    /**
     * Set the exception attribute.
     * 
     * @param  mixed  $value
     * @return void
     */
    public function setExceptionAttribute($value)
    {
        if($value instanceof Throwable) {
            $value = $value->getMessage();
        }

        $this->success = !($this->attributes['exception'] = $value);
    }

    /**
     * Determine if the status code is successful.
     * 
     * @return bool
     */
    public function isSuccessful()
    {
        return $this->status >= 200 && $this->status < 300;
    }
}