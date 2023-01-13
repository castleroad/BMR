<?php

namespace App\Http\Resources\Api\V1;

use App\Http\Resources\Api\V1\AttendeeResource;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => (string)$this->id,
            'title' => (string)$this->title,
            'startsAt' => (string)$this->starts_at->format('m/d/Y'),
            'endsAt' => (string)$this->ends_at->format('m/d/Y'),
            'allDay' => (string)$this->all_day,
            'timeFrom' => (string)$this->time_from,
            'timeTo' => (string)$this->time_to,
            'attendees' => AttendeeResource::collection($this->whenLoaded('users')),
        ];
    }
}
