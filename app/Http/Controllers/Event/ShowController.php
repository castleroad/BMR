<?php

namespace App\Http\Controllers\Event;

use Exception;
use App\Models\Event;

class ShowController extends BaseController
{
    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Event  $event
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Event $event)
    {
        $this->authorize('view', $event);
        
        try {
            $isOwner = in_array(request()->user()->id, $event->ownersIds());
        } catch (Exception $e) {
            return response()->json(['message' => 'There is no results for your request'], 404);
        }

        return view('events.show', compact('event', 'isOwner'));
    }
}
