<?php

namespace Actengage\LaravelPassendo;

use Actengage\LaravelPassendo\Exceptions\MethodNotDefined;
use Illuminate\Database\QueryException;

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
        try {
            return $this->conversions()->firstOrCreate([
                'tracking_id' => $tracking_id ?: $this->passendoTrackingId(),
            ], [
                'cpa' => $cpa ?: $this->passendoCpa()
            ]);    
        }
        catch (QueryException $e) {
            return $this->createPassendoConversion($tracking_id, $cpa);
        }
    }
    
    public static function bootTrackPassendoconversions()
    {
        static::saved(function($model) {
            $dispatch = method_exists($model, 'shouldTrackPassendoConversions')
                ? $model->shouldTrackPassendoConversions()
                : $model->validateTrackingId();


            if($dispatch) {
                $model->createPassendoConversion();
            }
        });
    }
}