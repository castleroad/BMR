<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\Event;
use Illuminate\Bus\Queueable;
use App\Notifications\Event\Update;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Notification;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class EventNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Event instance
     *
     * @var \App\Models\Event
     */
    public $event;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($event)
    {
        $this->event = $event;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $users = User::whereHas('events', function ($query) {
            $query->where('event_id', $this->event->id);
        })
        ->get();

        Notification::send($users, new Update($this->event));
    }
}
