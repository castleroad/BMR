<?php

namespace App\Services\Event;

use App\Events\Event\EventUpdated;
use App\Models\Event;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\Event\UpdateRequest;
use App\Jobs\EventEmailNotificationJob;
use App\Models\EventUser;
use App\Models\User;
use Illuminate\Support\Arr;

class Service
{
    /**
     * Event IndexController::__invoke helper
     *
     * @return Event
     */
    public function main()
    {
        $user = request()->user();
        
        $userEvents = Event::whereHas('users', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
        ->with(['users' => function ($query) use ($user) {
            $query->where('user_id', $user->id);
        }])
        ->get();

        return [
            $userEvents,
            $user->ownerEventsIds(),
        ];
    }

    /**
     * Event IndexController::__invoke helper
     *
     * @param App\Models\Event $event
     * @return App\Models\Event
     */
    public function edit(Event $event)
    {
        $event = Event::whereHas('users', function($query) use ($event) {
            $query->where('event_id', '=', $event->id);
        })
        ->with(['users' => function($query) {
            $query->orderBy('permission', 'asc');
        }])
        ->first();

        // Owner not shoving in multiselect
        $owner = $event->users[0];
        unset($event->users[0]);
        $users = User::all()->except($owner->id);

        $jsonAttendees = [];
        // Setting up json for multiselect
        foreach ($users as $user) {
            $selected = false;
            $permission = EventUser::PERMISSION_VIEW;
            $status = EventUser::STATUS_PENDING;

            foreach ($event->users as $eUser) {
                if ($user->id == $eUser->id) {
                    $selected = true;
                    $permission = $eUser->pivot->permission;
                    $status = $eUser->pivot->status;
                }
            }

            $jsonAttendees[] = [
                'id' => $user->id,
                'name' => $user->fullName(),
                'avatar' => $user->avatarUrl(),
                'permission' => $permission,
                'status' => $status,
                'disabled' => $selected,
                'selected' => $selected
            ];
        }
        $jsonAttendees = json_encode($jsonAttendees, JSON_UNESCAPED_UNICODE);

        return [$event, $owner, $jsonAttendees];
    }

    /**
     * Event DetachController::__invoke helper
     *
     * @param Event $event
     * @param User $user
     * @return void
     */
    public function detach(Event $event, User $user)
    {
        return DB::transaction(function () use ($user, $event) {
            $event->users()->detach($user->id);
            $event->touch();

            $event->detached_user = [$user->id, $user->fullName()];
            return $event;
        });
    }

    /**
     * Event DeleteController::__invoke helper
     *
     * @param Event $event
     * @return void
     */
    public function destroy(Event $event)
    {
        $detachedUsersIds = $event->usersIds();

        return DB::transaction(function () use ($event, $detachedUsersIds) {
            $event->users()->detach($detachedUsersIds);
            $event->delete();

            $event->detaches = $detachedUsersIds;
            return $event;
        });

    }

    /**
     * Event StoreController::__invoke helper
     *
     * @param array $validated
     * @return integer
     */
    public function store(array $validated)
    {
        $attachment = [];

        // Setting up event attachment according to user input
        foreach ($validated['attendees'] as $userId => $userOptions) {
            $attachment[$userId] = [
                'permission' => $userOptions['permission'],
                'status' => $userOptions['status'] ?? EventUser::STATUS_PENDING,
            ];
        }

        return DB::transaction(function () use ($validated, $attachment) {
            $event = Event::create($validated);
            $event->users()->attach($attachment);

            $event->attendees = $attachment;
            return $event;
        });
    }

    /**
     * Event UpdateController::__invoke helper
     *
     * @param  \App\Http\Requests\Event\UpdateRequest  $request
     * @param  \App\Models\Event  $event
     * @return void
     */
    public function update(UpdateRequest $request, Event $event)
    {
        $validated = $request->validated();
        $reqUserId = $request->user()->id;
        
        // Setting owner no matter what we got in user input
        foreach($event->users()->where('permission', '=', EventUser::PERMISSION_OWN)->get() as $owner) {
            if (!isset($validated['attendees'][$owner->id])) {
                $validated['attendees'][$owner->id] = [
                    'permission' => EventUser::PERMISSION_OWN,
                    'status' => $owner->pivot->status,
                ];
            } else {
                $validated['attendees'][$owner->id] = [
                    'permission' => EventUser::PERMISSION_OWN,
                    'status' => $validated['attendees'][$owner->id]['status'] ?? EventUser::STATUS_PENDING,
                ];
            }
        }

        // Is date or time was changed                
        if ($event->getOriginal('starts_at')->format('Y-m-d') != $validated['starts_at'] ||
            $event->getOriginal('ends_at')->format('Y-m-d') != $validated['ends_at'] ||
            $event->getOriginal('time_from') != ($validated['time_from'] ?? null) ||
            $event->getOriginal('time_to') != ($validated['time_to'] ?? null)
        ) {
            // If date or time changed reset attendees statuses
            $attachment = $this->editorGoingOtherPending($event, $reqUserId, $validated);
            // Special action occured
            EventUpdated::dispatch($event);
        } else {
            $attachment = $this->eventAttachment($event, $reqUserId, $validated);
        }
        
        return DB::transaction(function () use ($attachment, $event, $validated) {
            $event->update($validated);
            $event->users()
                ->where('event_id', $event->id)
                ->sync($attachment);

            $event->attendees = $attachment;
            return $event;
        });
    }

    /**
     * Reset statuses for event attendees
     *
     * @param Event $event
     * @param integer $reqUserId
     * @param array $validated
     * @return array
     */
    private function editorGoingOtherPending(Event $event, int $reqUserId, array $validated): array
    {
        $attachment = [];
        foreach ($validated['attendees'] as $userId => $userOptions) {
            if ($reqUserId == $userId) {
                $attachment[$userId] = [
                    'permission' => $event->users()->where('user_id', $userId)->first()?->pivot->getOriginal('permission') ?? EventUser::PERMISSION_VIEW,
                    'status' => EventUser::STATUS_GOING,
                ];
            }else{
                $attachment[$userId] = [
                    'permission' => $userOptions['permission'] ?? EventUser::PERMISSION_VIEW,
                    'status' => EventUser::STATUS_PENDING,
                ];
            }
        }
        return $attachment;
    }

    /**
     * Setting up event attachment according to user input
     *
     * @param Event $event
     * @param integer $reqUserId
     * @param array $validated
     * @return array
     */
    private function eventAttachment(Event $event, int $reqUserId, array $validated): array
    {
        $attachment = [];
        foreach ($validated['attendees'] as $userId => $userOptions) {
            if ($reqUserId == $userId) {
                $attachment[$userId] = [
                    'permission' => $event->users()->where('user_id', $userId)->first()?->pivot->getOriginal('permission') ?? EventUser::PERMISSION_VIEW,
                    'status' => $userOptions['status'] ?? EventUser::STATUS_PENDING,
                ];
            }else{
                $attachment[$userId] = [
                    'permission' => $userOptions['permission'] ?? EventUser::PERMISSION_VIEW,
                    'status' => $event->users()->where('user_id', $userId)->first()?->pivot->getOriginal('status') ?? EventUser::STATUS_PENDING,
                ];
            }
        }
        return $attachment;
    }
}