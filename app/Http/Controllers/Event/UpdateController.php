<?php

namespace App\Http\Controllers\Event;

use Exception;
use App\Models\Event;
use App\Http\Requests\Event\UpdateRequest;
use App\Http\Resources\Event\UpdateResource;

class UpdateController extends BaseController
{
    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\Event\UpdateRequest  $request
     * @param  \App\Models\Event  $event
     * @return \Illuminate\Http\JsonResponse
     */
    public function __invoke(UpdateRequest $request, Event $event)
    {
        $this->authorize('update', $event);
        try {
            return new UpdateResource($this->service->update($request, $event));
        } catch (Exception $e) {
            return response()->json(['message' => 'Something goes wrong with your request'], 500);
        }
    }

}
