<?php

namespace Actengage\LaravelPassendo\Jobs;

use Actengage\LaravelPassendo\Click;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class TrackClick implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels, Queueable;

    public $debug;

    /**
     * The passendo click being tracked.
     * 
     * @var \Actengage\LaravelPassendo\Click
     */
    public $click;
    
    /**
     * Delete the job if its models no longer exist.
     *
     * @var bool
     */
    public $deleteWhenMissingModels = true;
    
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Click $click)
    {
        $this->click = $click;
    }
    
    /**
     * Get the middleware the job should pass through.
     *
     * @return array
     */
    public function middleware()
    {
        return [new \Illuminate\Queue\Middleware\ThrottlesExceptions(1, 5)];
    }

    /**
     * Determine the time at which the job should timeout.
     *
     * @return \DateTime
     */
    public function retryUntil()
    {
        // Default to 2 days if not defined in config.
        return now()->addSeconds(config('passendo.retry_until', 172800));
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {   
        $this->click->track();
    }

    /**
     * Handle a job failure.
     *
     * @param  \Throwable  $exception
     * @return void
     */
    public function failed(Throwable $exception)
    {
        $this->click->failed($exception);
    }
}
