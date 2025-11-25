<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class AssignmentResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'             => $this->id,
            'class_id'       => $this->class_id,
            'title'          => $this->title,
            'description'    => $this->description,
            'instructions'   => $this->instructions,
            'deadline'       => $this->deadline,
            'max_marks'      => $this->max_marks,
            'assignment_type' => $this->assignment_type,
            'is_published'   => $this->is_published,

            'file_name'      => $this->file_name,
            'file_path'      => $this->file_path,
            'file_url' => $this->file_path
                ? asset('storage/' . $this->file_path)
                : null,

            'created_at'     => $this->created_at,
            'updated_at'     => $this->updated_at,
        ];
    }
}