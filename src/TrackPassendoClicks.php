<?php

namespace Actengage\LaravelPassendo;

use Actengage\LaravelPassendo\Exceptions\MethodNotDefined;
use Exception;

trait TrackPassendoClicks {

    public function clicks()
    {
        return $this->morphMany(Click::class, 'parent');
    }

    public function passendoCpa(): float
    {
        if(method_exists($this, 'cpa')) {
            return (float) $this->cpa();
        }

        throw new MethodNotDefined('cpa');
    }

    public function passendoTrackingId(): ?string
    {
        if(method_exists($this, 'tid')) {
            return (string) $this->tid();
        }

        throw new MethodNotDefined('tid');
    }

    public function createPassendoClick(string $tracking_id = null, float $cpa = null): Click
    {
        return $this->clicks()->firstOrCreate([
            'cpa' => $cpa ?: $this->passendoCpa(),
            'tracking_id' => $tracking_id ?: $this->passendoTrackingId(),
        ]);
    }
    
    public static function bootTrackPassendoClicks()
    {
        $triggers = method_exists(__CLASS__, 'definePassendoTriggers')
            ? static::definePassendoTriggers()
            : ['created'];
        
        foreach($triggers as $trigger) {
            static::$trigger(function($model) {
                $dispatch = method_exists($model, 'shouldTrackPassendoClicks')
                     ? $model->shouldTrackPassendoClicks()
                     : true;

                $dispatch && $model->createPassendoClick();
            }); 
        }
    }
}