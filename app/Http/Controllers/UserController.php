<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Event;
use App\Models\EventUser;
use Illuminate\Http\Request;
use App\Http\Filters\UserFilter;
use Illuminate\Support\Facades\App;
use App\Http\Requests\User\FilterRequest;
use App\Http\Resources\User\SearchResource;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function search(FilterRequest $request)
    {
        $params = $request->validated();
        
        $filter = App::make(UserFilter::class, ['queryParams' => $params]);
        $users = User::filter($filter)->get();
        
        return SearchResource::collection($users);
    }

    /**
     * Print new event tr for table on index page
     *
     * @param Event $event
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh(Event $event)
    {
        $user = request()->user();

        $event = Event::with(['users' => function ($query) use ($user) {
            $query->where('user_id', $user->id);
        }])
        ->whereId($event->id)
        ->first();
        
        $statuses = EventUser::statuses();
        $permissions = EventUser::permissions();
        $userOwnerEventsIds = $user->ownerEventsIds();

        return response()->json([
            'tr' => view('users.event_refresh', compact('event', 'statuses', 'permissions', 'userOwnerEventsIds'))->render(),
        ]);
    }

    /**
     * Attendees paginate for eventViewModal
     *
     * @param Event $event
     * @return \Illuminate\Http\JsonResponse
     */
    public function paginate(Event $event)
    {
        $attendees = User::whereHas('events', function($query) use ($event) {
            $query->where('event_id', '=', $event->id);
        })
        ->with(['events' => function ($query) use ($event) {
            $query->where('event_id', $event->id);
            $query->orderBy('permission', 'asc');
        }])
        ->paginate();

        $statuses = EventUser::statuses();
        $permissions = EventUser::permissions();

        return response()->json([
            'attendees' => view('users.paginate', compact('attendees', 'statuses', 'permissions'))->render(),
        ]);
    }
    /**
     * Mark notifications as read
     *
     * @param Request $request
     * @return void
     */
    public function markNotification(Request $request)
    {
        $request->user()
            ->unreadNotifications
            ->when($request->input('id'), function ($query) use ($request) {
                return $query->where('id', $request->input('id'));
            })
            ->markAsRead();

        return response()->noContent();
    }
}
