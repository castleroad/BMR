<?php

namespace App\Http\Resources\Event;

use App\Jobs\EventNotificationJob;
use Illuminate\Http\Resources\Json\JsonResource;

class DestroyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $detaches = [];
        foreach ($this->detaches as $userId) {
            $detaches[] = [
                'user_id' => $userId,
            ];
        }
        
        // Please set up your .env file QUEUE_CONNECTION to database
        dispatch(new EventNotificationJob($this));

        return [
            'message' => 'The '.$this->title.' Event was successfully deleted',
            'id' => (string)$this->id,
            'title' => $this->title,
            'starts_at' => $this->starts_at,
            'ends_at' => $this->ends_at,
            'all_day' => $this->all_day,
            'time_from' => $this->time_from,
            'time_to' => $this->time_to,
            'detaches' => $detaches,
            'refresh_url' => false,
        ];
    }
}
