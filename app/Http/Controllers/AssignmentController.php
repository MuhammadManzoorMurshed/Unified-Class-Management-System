<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Classes;
use App\Models\AuditLogs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AssignmentController extends Controller
{
    /**
     * নির্দিষ্ট ক্লাসের সব assignment লিস্ট (Teacher/Student উভয়ই দেখতে পারবে)
     */
    public function index($classId)
    {
        $class = Classes::findOrFail($classId);

        $assignments = Assignment::where('class_id', $class->id)
            ->orderBy('deadline', 'asc')
            ->get();

        return response()->json([
            'message' => 'Assignments fetched successfully.',
            'data'    => \App\Http\Resources\AssignmentResource::collection($assignments),
        ]);
    }

    /**
     * নতুন assignment create (শুধু Admin/Teacher, এবং নিজের ক্লাস হলে)
     */
    public function store(Request $request, $classId)
    {
        $user  = auth('api')->user();
        $class = Classes::findOrFail($classId);

        // --- Only class teacher can create assignment ---
        if ($user->role->role_name === 'Teacher' && $class->teacher_id !== $user->id) {
            return response()->json([
                'message' => 'You can only create assignments for your own classes.',
            ], 403);
        }

        $validated = $request->validate([
            'title'           => 'required|string|max:200',
            'description'     => 'required|string',
            'instructions'    => 'nullable|string',
            'deadline'        => 'required|date|after:now',
            'max_marks'       => 'nullable|numeric|min:0|max:999.99',
            'assignment_type' => 'required|string|in:Homework,Assignment,Lab Report,Project Proposal,Project Report,Project,Thesis',
            'is_published'    => 'sometimes|boolean',

            // ⬇️ file validation
            'file'            => 'nullable|file|max:10240', // 10MB
        ]);

        // --- Step 1: create assignment first ---
        $assignment = Assignment::create([
            'class_id'        => $class->id,
            'title'           => $validated['title'],
            'description'     => $validated['description'],
            'instructions'    => $validated['instructions'] ?? null,
            'deadline'        => $validated['deadline'],
            'max_marks'       => $validated['max_marks'] ?? 100,
            'assignment_type' => $validated['assignment_type'],
            'is_published'    => $validated['is_published'] ?? false,
        ]);

        // --- Step 2: Handle File Upload (optional) ---
        if ($request->hasFile('file')) {

            $file = $request->file('file');

            // Save to storage/app/public/assignments/
            $path = $file->store('assignments', 'public');

            // Update assignment record
            $assignment->update([
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
            ]);
        }

        // --- Step 3: Audit Log ---
        AuditLogs::create([
            'action'      => 'assignment.create',
            'entity_type' => 'Assignment',
            'entity_id'   => $assignment->id,
            'meta'        => json_encode(['class_id' => $class->id]),
            'user_id'     => $user->id,
            'ip_address'  => $request->ip(),
        ]);

        return response()->json([
            'message' => 'Assignment created successfully.',
            'data'    => new \App\Http\Resources\AssignmentResource($assignment),
        ], 201);
    }
}