<?php

namespace App\Listeners\Event;

use App\Models\User;
use App\Events\Event\EventUpdated;
use App\Notifications\Event\DateOrTimeChanged;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;

class EventDateWasUpdatedNotification
{
    /**
     * When to send notification
     *
     * @var boolean
     */
    public $afterCommit = true;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\Event\EventUpdated  $event
     * @return void
     */
    public function handle(EventUpdated $event)
    {
        $users = User::whereHas('events', function ($query) use ($event) {
            $query->where('event_id', $event->event->id);
        })
        ->get();

        Notification::send($users, new DateOrTimeChanged($event->event));
    }
}
