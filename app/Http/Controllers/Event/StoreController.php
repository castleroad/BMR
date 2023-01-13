<?php

namespace App\Http\Controllers\Event;

use Exception;
use App\Http\Requests\Event\StoreRequest;
use App\Http\Resources\Event\StoreResource;

class StoreController extends BaseController
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\Event\StoreRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function __invoke(StoreRequest $request)
    {
        try {
            return new StoreResource($this->service->store($request->validated()));
        } catch (Exception $e) {
            return response()->json(['message' => 'Something goes wrong with your request'], 500);
        }
    }
}
