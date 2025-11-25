<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ClassResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'           => $this->id,
            'name'         => $this->name,
            'code'         => $this->code,
            'subject'      => $this->subject,
            'description'  => $this->description,
            'semester'     => $this->semester,
            'year'         => $this->year,
            'max_students' => $this->max_students,
            'is_active'    => $this->is_active,

            // activeEnrollments withCount থেকে আসবে
            'member_count' => $this->member_count ?? null,

            'teacher'      => [
                'id'    => optional($this->teacher)->id,
                'name'  => optional($this->teacher)->name,
                'email' => optional($this->teacher)->email,
            ],

            'students'     => $this->whenLoaded('activeEnrollments', function () {
                return $this->activeEnrollments
                    ->filter(function ($enrollment) {
                        return $enrollment->user !== null;
                    })
                    ->map(function ($enrollment) {
                        return [
                            'id'    => $enrollment->user->id,
                            'name'  => $enrollment->user->name,
                            'email' => $enrollment->user->email,
                        ];
                    })
                    ->values();
            }),
        ];
    }
}