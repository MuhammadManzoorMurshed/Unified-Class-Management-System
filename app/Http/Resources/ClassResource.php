<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ClassResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'subject' => $this->subject,
            'semester' => $this->semester,
            'year' => $this->year,
            'max_students' => $this->max_students,
            'is_active' => $this->is_active,
            'teacher' => [
                'id' => $this->teacher->id,
                'name' => $this->teacher->name,
            ],
        ];
    }
}