<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class AttendeeResource extends JsonResource
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
            'userId' => (string)$this->id,
            'name' => (string)$this->fullName(),
            'email' => (string)$this->email,
            'attributes' => [
                'permission' => (string)$this->pivot->permission,
                'status' => (string)$this->pivot->status,
            ],
        ];
    }
}
