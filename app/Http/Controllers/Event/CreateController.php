<?php

namespace App\Http\Controllers\Event;

use App\Models\User;
use App\Models\EventUser;

class CreateController extends BaseController
{
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __invoke()
    {
        $owner = request()->user();
        $users = User::all()->except($owner->id);
        $statuses = EventUser::statuses();

        return view('events.create', compact('owner', 'users', 'statuses'));
    }

}
