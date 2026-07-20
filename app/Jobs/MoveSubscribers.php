<?php

namespace Acelle\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class MoveSubscribers implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected $subscribers;
    protected $fromList;
    protected $toList;

    public $timeout = 7200;
    public $maxExceptions = 1; // This is required if retryUntil is used, otherwise, the default value is 255
    public $failOnTimeout = true;

    /**
     * Create a new job instance.
     */
    public function __construct($subscribers, $fromList, $toList)
    {
        $this->subscribers = $subscribers;
        $this->fromList = $fromList;
        $this->toList = $toList;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        foreach ($this->subscribers as $subscriber) {
            $subscriber->move($this->toList);
        }

        // Trigger updating related campaigns cache
        event(new \Acelle\Events\MailListUpdated($this->fromList));
        event(new \Acelle\Events\MailListUpdated($this->toList));

        // Log
        $this->toList->log('moved', $this->toList->customer, [
            'count' => $this->subscribers->count(),
            'from_uid' => $this->fromList->uid,
            'to_uid' => $this->toList->uid,
            'from_name' => $this->fromList->name,
            'to_name' => $this->toList->name,
        ]);
    }
}
