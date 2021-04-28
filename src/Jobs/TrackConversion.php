<?php

namespace Actengage\LaravelPassendo\Jobs;

use Actengage\LaravelPassendo\Conversion;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class TrackConversion implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels, Queueable;

    public $debug;

    /**
     * The passendo conversion being tracked.
     * 
     * @var \Actengage\LaravelPassendo\Conversion
     */
    public $conversion;
    
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
    public function __construct(Conversion $conversion)
    {
        $this->conversion = $conversion;
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
        $this->conversion->track();
    }

    /**
     * Handle a job failure.
     *
     * @param  \Throwable  $exception
     * @return void
     */
    public function failed(Throwable $exception)
    {
        $this->conversion->failed($exception);
    }
}
