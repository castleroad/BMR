<?php

namespace App\Http\Controllers\Event;

use App\Models\User;
use App\Models\Event;
use App\Models\EventUser;
use Exception;

class EditController extends BaseController
{
    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Event  $event
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Event $event)
    {
        $this->authorize('update', $event);
        
        try {
            list($event, $owner, $jsonAttendees) = $this->service->edit($event);
        } catch (Exception $e) {
            return response()->json(['message' => 'There is no results for your request'], 404);
        }

        $requestUser = request()->user();
        $statuses = EventUser::statuses();
        
        return view('events.edit', compact('event', 'owner', 'jsonAttendees', 'requestUser', 'statuses'));
    }
}
