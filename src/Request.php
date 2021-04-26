<?php

namespace Actengage\LaravelPassendo;

use Actengage\LaravelPassendo\Jobs\SendHttpRequest;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use Illuminate\Database\Eloquent\Model;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class Request extends Model {

    use HandlesRequest;

    /**
     * The mock handler stack.
     * 
     * @static
     * @var \GuzzleHttp\HandlerStack
     */
    static protected $handler;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'passendo_requests';

    /**
     * The attributes that are fillable.
     *
     * @var string
     */
    protected $fillable = [
        'success',
        'status',
        'exception',
        'sent_at',
        'received_at',
    ];

    /**
     * Get the parent click.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function click()
    {
        return $this->belongsTo(Click::class, 'click_id');
    }

    /**
     * Add the `succcess` scope the query.
     * 
     * @param  mixed  $query
     * @return void
     */
    public function scopeSuccess($query)
    {
        $query->whereSuccess(true);
    }

    /**
     * Add the `failed` scope the query.
     * 
     * @param  mixed  $query
     * @return void
     */
    public function scopeFailed($query)
    {
        $query->whereSuccess(false);
    }
    
    /**
     * Get the HTTP client.
     * 
     * @return \GuzzleHttp\Client
     */
    public function client()
    {
        return new Client(['handler' => static::$handler]);
    }
    
    /**
     * Send the HTTP request to Passendo.
     * 
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function send()
    {
        // Update the send_at to the current time.
        $this->sent_at = now();
        $this->save();

        // Return a GET response.
        return $this->client()->get($this->trackingUri());
    }

    /**
     * Get the uri from the parent click.
     *
     * @return string
     */
    public function trackingUri()
    {
        return $this->click->trackingUri();
    }
    
    /**
     * Handle a successful response.
     * 
     * @param \Psr\Http\Message\ResponseInterface
     * @return void
     */
    public function success(ResponseInterface $response)
    {
        $this->fill([
            'status' => $response,
            'received_at' => now(),
        ]);
        
        $this->save();

        $this->click->success($response);
    }
    
    /**
     * Handle an exception.
     * 
     * @param \Throwable  $e
     * @return void
     */
    public function failed(Throwable $e)
    {
        $this->fill([
            'status' => $e,
            'exception' => $e,
            'received_at' => now(),
        ])->save();
        
        $this->click->failed($e);
    }

    /**
     * Set a mock hander.
     * 
     * @param  \GuzzleHttp\Handler\MockHandler  $handler
     * @return \GuzzleHttp\HandlerStack
     */
    public static function mock(MockHandler $handler)
    {
        return static::$handler = HandlerStack::create($handler);
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
            $model->click->total_requests = $model->click->requests()->count();
            $model->click->save();

            SendHttpRequest::dispatch($model);
        });
    }  
}