<?php

namespace Actengage\LaravelPassendo;

use Actengage\LaravelPassendo\Exceptions\InvalidTrackingId;
use Actengage\LaravelPassendo\Jobs\TrackClick;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class Click extends Model {
    
    use HandlesRequest;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'passendo_clicks';  

    /**
     * The attributes that are fillable.
     *
     * @var string
     */
    protected $casts = [
        'status' => 'int',
        'success' => 'bool'
    ]; 

    /**
     * The attributes that are fillable.
     *
     * @var string
     */
    protected $fillable = [
        'tracking_id',
        'cpa',
        'total_requests',
        'success',
        'status',
        'exception'
    ]; 

    /**
     * Get the many requests.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function requests(): HasMany
    {
        return $this->hasMany(Request::class);
    }

    /**
     * Get the many requests.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function parent(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the tracking uri.
     *
     * @return string
     */
    public function trackingUri()
    {
        return "http://images.passendo.com/ss/{$this->tracking_id}/{$this->cpa}";
    }

    /**
     * Determine if the tracking uri is active.
     *
     * @return string
     */
    public function validate()
    {
        return preg_match('/^\@.+\@$/', $this->tracking_id) === 0;
    }
    
    /**
     * Send a new HTTP request to Passendo.
     * 
     * @return \Actengage\LaravelPassendo\Request
     */
    public function track(): Request
    {
        // First check to see if the click was already tracked...
        if($this->success) {
            return $this->requests()->success()->firstOrFail();
        }

        // Validate the tracking id.
        if(!$this->validate()) {
            throw new InvalidTrackingId($this);
        }

        // Create a new request for the queue...
        return $this->requests()->create();
    }
    
    /**
     * Handle a successful response.
     * 
     * @param \Actengage\LaravelPassendo\Request
     * @param \Psr\Http\Message\ResponseInterface
     * @return void
     */
    public function success(ResponseInterface $response)
    {
        $this->fill([
            'status' => $response
        ])->save();
    }
    
    /**
     * Handle an exception.
     * 
     * @param \Actengage\LaravelPassendo\Request
     * @param \Throwable  $e
     * @return void
     */
    public function failed(Exception $e)
    {
        $this->fill([
            'status' => $e,
            'exception' => $e,
        ])->save();
    }

    /**
     * Dispatch the tracker.
     * 
     * @return void
     */
    public function dispatch($debug = false)
    {
        TrackClick::dispatch($this, $debug);
    }
    
    /**
     * Boot the model.
     * 
     * @return void
     */
    public static function boot()
    {
        parent::boot();

        parent::created(function($model) {
            $model->dispatch();
        });
    }    
}