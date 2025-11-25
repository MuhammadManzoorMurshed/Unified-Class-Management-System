<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Submission;
use App\Models\Enrollment;
use App\Models\AuditLogs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\SubmissionResource;


class SubmissionController extends Controller
{
    /**
     * Student → নির্দিষ্ট assignment-এ নিজের submission দেখা
     */
    public function showMySubmission($assignmentId)
    {
        $user       = auth('api')->user();
        $assignment = Assignment::findOrFail($assignmentId);

        $submission = Submission::where('assignment_id', $assignment->id)
            ->where('student_id', $user->id)
            ->first();

        if (!$submission) {
            return response()->json([
                'message' => 'No submission found for this assignment.',
            ], 404);
        }

        return response()->json([
            'message' => 'Submission fetched successfully.',
            'data'    => new SubmissionResource($submission),
        ]);
    }

    /**
     * Teacher/Admin → নির্দিষ্ট assignment-এর সব submission list
     */
    public function index($assignmentId)
    {
        $user       = auth('api')->user();
        $assignment = Assignment::with('classroom')->findOrFail($assignmentId);

        // Teacher হলে তার নিজের ক্লাস হওয়া লাগবে; Admin সব দেখতে পারবে
        if (
            $user->role->role_name === 'Teacher' &&
            $assignment->classroom &&
            $assignment->classroom->teacher_id !== $user->id
        ) {
            return response()->json([
                'message' => 'You can only view submissions for your own classes.',
            ], 403);
        }

        $submissions = Submission::where('assignment_id', $assignment->id)
            ->with('student')
            ->orderBy('submission_date', 'asc')
            ->get();

        return response()->json([
            'message' => 'Submissions fetched successfully.',
            'data'    => SubmissionResource::collection($submissions),
        ]);
    }


    /**
     * Student → assignment submission (file upload সহ)
     */
    public function store(Request $request, $assignmentId)
    {
        $user       = auth('api')->user();
        $assignment = Assignment::findOrFail($assignmentId);

        // কনফার্ম করি student কি ক্লাসে enrolled আছে
        $isEnrolled = Enrollment::where('user_id', $user->id)
            ->where('class_id', $assignment->class_id)
            ->where('status', 'active')
            ->exists();

        if (!$isEnrolled) {
            return response()->json([
                'message' => 'You are not enrolled in this class.',
            ], 403);
        }

        $validated = $request->validate([
            'file' => 'required|file|max:10240', // 10 MB
        ]);

        $file = $validated['file'];

        // storage/app/public/submissions এ ফাইল সেভ হবে
        $path         = $file->store('submissions', 'public');
        $originalName = $file->getClientOriginalName();

        $now    = now();
        $status = $now->greaterThan($assignment->deadline) ? 'late' : 'submitted';

        // একই student একই assignment-এ আবার submit করলে → update হবে
        $submission = Submission::updateOrCreate(
            [
                'assignment_id' => $assignment->id,
                'student_id'    => $user->id,
            ],
            [
                'file_path'       => $path,
                'file_name'       => $originalName,
                'submission_date' => $now,
                'status'          => $status,
            ]
        );

        AuditLogs::create([
            'action'      => 'submission.store',
            'entity_type' => 'Submission',
            'entity_id'   => $submission->id,
            'meta'        => json_encode([
                'assignment_id' => $assignment->id,
                'status'        => $status,
            ]),
            'user_id'     => $user->id,
            'ip_address'  => $request->ip(),
        ]);

        return response()->json([
            'message' => 'Assignment submitted successfully.',
            'data'    => $submission,
        ], 201);
    }

    public function updateMarks(Request $request, $submissionId)
    {
        $user = auth('api')->user();

        if (!in_array($user->role->role_name, ['Teacher', 'Admin'])) {
            return response()->json([
                'message' => 'Only teachers or admins can update marks.'
            ], 403);
        }

        $submission = Submission::with('assignment.classroom')->findOrFail($submissionId);

        if ($user->role->role_name === 'Teacher') {
            $assignmentClass = $submission->assignment->classroom ?? null;
            if (!$assignmentClass || $assignmentClass->teacher_id !== $user->id) {
                return response()->json([
                    'message' => 'You can only mark submissions for your own classes.'
                ], 403);
            }
        }

        $data = $request->validate([
            'marks' => 'nullable|numeric|min:0|max:999.99',
        ]);

        $submission->marks_obtained = $data['marks'];
        $submission->save();

        return response()->json([
            'message' => 'Marks updated successfully.',
            'data'    => new SubmissionResource($submission),
        ]);
    }


    public function viewFile(Submission $submission)
    {
        $user = auth('api')->user();

        // Teacher/Admin ছাড়া কেউ submissions দেখতে পারবে না
        if (!in_array($user->role->role_name, ['Teacher', 'Admin'])) {
            abort(403, 'Forbidden');
        }

        // Teacher হলে নিজের ক্লাস কিনা চেক
        $assignment = $submission->assignment()->with('classroom')->first();
        if ($user->role->role_name === 'Teacher') {
            if (!$assignment || !$assignment->classroom || $assignment->classroom->teacher_id !== $user->id) {
                abort(403, 'Forbidden');
            }
        }

        if (!$submission->file_path || !Storage::disk('public')->exists($submission->file_path)) {
            abort(404, 'File not found.');
        }

        $path     = Storage::disk('public')->path($submission->file_path);
        $filename = $submission->file_name ?? basename($submission->file_path);

        return response()->file($path, [
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }

    public function downloadFile(Submission $submission)
    {
        $user = auth('api')->user();

        if (!in_array($user->role->role_name, ['Teacher', 'Admin'])) {
            abort(403, 'Forbidden');
        }

        $assignment = $submission->assignment()->with('classroom')->first();
        if ($user->role->role_name === 'Teacher') {
            if (!$assignment || !$assignment->classroom || $assignment->classroom->teacher_id !== $user->id) {
                abort(403, 'Forbidden');
            }
        }

        if (!$submission->file_path || !Storage::disk('public')->exists($submission->file_path)) {
            abort(404, 'File not found.');
        }

        $filename = $submission->file_name ?? basename($submission->file_path);

        return response()->download(
            storage_path('app/public/' . $submission->file_path),
            $submission->file_name
        );
    }
}