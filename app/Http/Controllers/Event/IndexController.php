<?php

namespace App\Http\Controllers\Event;

use Exception;

class IndexController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __invoke()
    {
        try {
            list($userEvents, $userOwnerEventsIds) = $this->service->main();
        } catch (Exception $e) {
            return response()->json(['message' => 'There is no results for your request'], 404);
        }
        
        return view('events.index', compact('userEvents', 'userOwnerEventsIds'));
    }
}
