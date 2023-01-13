<?php

namespace App\Http\Controllers\Event;

use Exception;
use App\Models\Event;
use App\Http\Resources\Event\DetachResource;

class DetachController extends BaseController
{
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Event  $event
     * @return \Illuminate\Http\JsonResponse
     */
    public function __invoke(Event $event)
    {
        $this->authorize('view', $event);
        try {
            return new DetachResource($this->service->detach($event, request()->user()));
        } catch (Exception $e) {
            return response()->json(['message' => 'There is no results for your request'], 404);
        }
    }
}
