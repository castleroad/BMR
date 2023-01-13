<?php

namespace App\Http\Resources\Event;

use App\Jobs\EventNotificationJob;
use Illuminate\Http\Resources\Json\JsonResource;

class UpdateResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $attendees = [];
        foreach ($this->attendees as $userId => $userOptions) {
            $attendees[] = [
                'user_id' => $userId,
                'permission' => $userOptions['permission'] ?? 'No permission',
                'status' => $userOptions['status'] ?? 'No status',
            ];
        }

        // Please set up your .env file QUEUE_CONNECTION to database
        dispatch(new EventNotificationJob($this));
        
        return [
            'id' => (string)$this->id,
            'title' => $this->title,
            'starts_at' => $this->starts_at,
            'ends_at' => $this->ends_at,
            'all_day' => $this->all_day,
            'time_from' => $this->time_from,
            'time_to' => $this->time_to,
            'attendees' => $attendees,
            'refresh_url' => route('users.event_refresh', $this->id),
        ];
    }
}
