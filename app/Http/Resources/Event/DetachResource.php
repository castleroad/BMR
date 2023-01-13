<?php

namespace App\Http\Resources\Event;

use App\Jobs\EventNotificationJob;
use Illuminate\Http\Resources\Json\JsonResource;

class DetachResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {

        // Please set up your .env file QUEUE_CONNECTION to database
        dispatch(new EventNotificationJob($this));
        
        return [
            'message' => 'User '.$this->detached_user[1].' was detached from the '.$this->title.' Event',
            'user_id' => (string)$this->detached_user[0],
            'event_id' => (string)$this->id,
            'refresh_url' => false,
        ];
    }
}
