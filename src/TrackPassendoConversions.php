<?php

namespace Actengage\LaravelPassendo;

use Actengage\LaravelPassendo\Exceptions\MethodNotDefined;

trait TrackPassendoConversions {

    public function conversions()
    {
        return $this->morphMany(Conversion::class, 'parent');
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

    public function validateTrackingId(?string $tracking_id = null): bool
    {
        return ($tid = $tracking_id ?: $this->tid()) && !preg_match('/^@.+@$/', $tid);
    }

    public function createPassendoConversion(?string $tracking_id = null, ?float $cpa = null): Conversion
    {
        $conversion = $this->conversions()->firstOrNew([
            'tracking_id' => $tracking_id ?: $this->passendoTrackingId(),
        ]);

        if(!$conversion->cpa) {
            $conversion->cpa = $cpa ?: $this->passendoCpa();
        }
        
        $conversion->save();

        return $conversion;
    }
    
    public static function bootTrackPassendoconversions()
    {
        static::created(function($model) {
            $dispatch = method_exists($model, 'shouldTrackPassendoConversions')
                ? $model->shouldTrackPassendoConversions()
                : $model->validateTrackingId();


            $dispatch && $model->createPassendoConversion();
        });
    }
}