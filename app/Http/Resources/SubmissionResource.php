<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class SubmissionResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'             => $this->id,
            'student_id'     => $this->student_id,
            'assignment_id'  => $this->assignment_id,
            'file_name'      => $this->file_name,
            'file_path'      => $this->file_path,
            // 👉 সরাসরি public storage URL (এখানে কোনো auth দরকার নেই)
            'file_url' => $this->file_path
                ? asset('storage/' . $this->file_path)
                : null,


            'submitted_at'   => $this->submission_date ?? $this->created_at,
            'status'         => $this->status,
            'marks_obtained' => $this->marks_obtained,
            'marks'          => $this->marks_obtained,
            'student'        => $this->whenLoaded('student'),
        ];
    }
}