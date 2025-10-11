<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => [
                'id' => $this->role->id,
                'name' => $this->role->name,
            ],
            'avatar' => $this->avatar,
            'status' => $this->status,
            'preferences' => $this->preferences,
            'created_at' => $this->created_at,
        ];
    }
}