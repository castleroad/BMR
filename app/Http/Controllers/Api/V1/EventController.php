<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Event;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Event\StoreRequest;
use App\Http\Requests\Api\V1\Event\UpdateRequest;
use App\Http\Resources\Api\V1\EventResource;
use App\Models\EventUser;
use Exception;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $userEvents = Event::whereHas('users', function ($query) {
            $query->where('user_id', request()->user()->id);
        });
        
        if (request()->query('withUsers')){
            $userEvents->with('users');
        }

        return EventResource::collection($userEvents->paginate());
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return response()->json(['message' => 'You are a creator']);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRequest $request)
    {
        $validated = $request->validated();
        $event = Event::create($validated);
        
        $attachments = [];
        foreach ($validated['attendees'] as $userOptions) {
            $attachments[$userOptions['userId']] = [
                'permission' => $userOptions['attributes']['permission'] ?? EventUser::PERMISSION_VIEW,
                'status' => $userOptions['attributes']['status'] ?? EventUser::STATUS_PENDING,
            ];
        } 
        $event->users()->attach($attachments);

        return new EventResource($event->with('users')->whereId($event->id)->first());
    }

    /**
     * Store a multiple newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function multiStore(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (!Event::whereId($id)->exists()) {
            return response()->json(['message' => 'There is no event with id = '.$id], 404);
        }

        $event = Event::whereId($id);

        if(request()->query('withUsers')) {
            $event->with('users');
        }

        return new EventResource($event->first());
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        return response()->json(['message' => 'You are a editor']);
    }

    /**
     * Update the specified resource in storage.
     * 
     * For now works only in PUT logic
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRequest $request, $id)
    {
        if (!Event::whereId($id)->exists()) {
            return response()->json(['message' => 'There is no event with id = '.$id], 404);
        }

        $validated = $request->validated();
        $event = Event::whereId($id)->with('users')->first();
        
        $event->update($validated);

        if (!isset($validated['attendees']) || !count($validated['attendees'])) {
            return new EventResource($event);
        }

        $sync = [];        
        foreach ($event->users as $user) {
            foreach ($validated['attendees'] as $key => $userInfo) {

                if ($userInfo['userId'] != $user->id) {
                    continue;
                }
                
                $sync[$user->id] = [
                    'permission' => $userInfo['attributes']['permission'] ?? $user->pivot->permission,
                    'status' => $userInfo['attributes']['status'] ?? $user->pivot->status,
                ];

                unset($validated['attendees'][$key]);
            }
        } 

        if (!count($validated['attendees'])) {
            $event->users()->sync($sync);
            return new EventResource($event->with('users')->whereId($event->id)->first());
        } else {
            $event->users()->sync($sync);
        }

        $attachments = [];
        foreach ($validated['attendees'] as $userInfo) {
            $attachments[$userInfo['userId']] = [
                'permission' => $userInfo['attributes']['permission'] ?? EventUser::PERMISSION_VIEW,
                'status' => $userInfo['attributes']['status'] ?? EventUser::STATUS_PENDING,
            ];
        }
        $event->users()->attach($attachments);

        return new EventResource($event->with('users')->whereId($event->id)->first());
    }

    /**
     * Update the multiple specified resources in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function multipleUpdate(Request $request, $id)
    {
        //
    }

    /**
     * Detach user from event.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function detach($id)
    {
        if (!Event::whereId($id)->exists()) {
            return response()->json(['message' => 'There is no event with id = '.$id], 404);
        }

        $event = Event::whereId($id)->whereHas('users', function($query) {
            $query->where('permission', '<>', EventUser::PERMISSION_OWN);
        })
        ->first();

        try {
            $event->users()->detach(request()->user()->id);
            $event->touch();
        } catch (Exception $e) {
            return response()->json(['message' => 'Something is going wrongwith your request'], 500);
        }

        return response()->json(['message' => 'Detached successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!Event::whereId($id)->exists()) {
            return response()->json(['message' => 'There is no event with id = '.$id], 404);
        }
        
        $event = Event::whereId($id)->whereHas('users', function($query) {
            $query->where('permission', EventUser::PERMISSION_OWN);
        })
        ->first();

        try {
            $event->users()->detach();
            $event->delete();
        } catch (Exception $e) {
            return response()->json(['message' => 'Something is going wrongwith your request'], 500);
        }

        return response()->json(['message' => 'Deleted successfully']);
    }
}
