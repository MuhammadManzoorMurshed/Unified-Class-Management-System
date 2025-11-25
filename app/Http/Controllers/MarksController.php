<?php

namespace App\Http\Controllers;

use App\Models\Mark;
use App\Models\Exam;
use App\Models\Enrollment;
use Illuminate\Http\Request;

class MarksController extends Controller
{
    // Teacher/Admin → Enter/Update Marks
    public function store(Request $request, $examId)
    {
        $user = auth('api')->user();
        $exam = Exam::with('classroom')->findOrFail($examId);

        if (
            $user->role->role_name === 'Teacher' &&
            $exam->classroom &&
            $exam->classroom->teacher_id !== $user->id
        ) {
            return response()->json(['message' => 'Access denied.'], 403);
        }

        $validated = $request->validate([
            'marks' => 'required|array',
            'marks.*.student_id' => 'required|integer|exists:users,id',
            'marks.*.marks_obtained' => 'required|numeric|min:0|max:' . $exam->total_marks,
            'marks.*.grade' => 'nullable|string|max:5',
        ]);

        foreach ($validated['marks'] as $row) {

            $isEnrolled = Enrollment::where('user_id', $row['student_id'])
                ->where('class_id', $exam->classroom->id)
                ->where('status', 'active')
                ->exists();

            if (!$isEnrolled) continue;

            Mark::updateOrCreate(
                [
                    'exam_id'    => $examId,
                    'student_id' => $row['student_id'],
                ],
                [
                    'marks_obtained' => $row['marks_obtained'],
                    'grade'          => $row['grade'] ?? null,
                    'entered_by'     => $user->id,
                ]
            );
        }

        return response()->json([
            'message' => 'Marks saved successfully.',
        ]);
    }

    // Exam Marks Overview
    public function examMarks($examId)
    {
        $user = auth('api')->user();
        $exam = Exam::with('classroom')->findOrFail($examId);

        // Teacher হলে নিজের ক্লাস হতে হবে
        if (
            $user->role->role_name === 'Teacher' &&
            $exam->classroom &&
            $exam->classroom->teacher_id !== $user->id
        ) {
            return response()->json(['message' => 'Access denied.'], 403);
        }

        // Exam-এর সমস্ত মার্কস + student তথ্য
        $marks = Mark::where('exam_id', $examId)
            ->with('student:id,name,email')
            ->orderBy('student_id', 'asc')
            ->get();

        return response()->json([
            'message' => 'Exam marks fetched successfully.',
            'exam'    => [
                'id'          => $exam->id,
                'title'       => $exam->title,
                'exam_type'   => $exam->exam_type,
                'exam_date'   => $exam->exam_date,
                'total_marks' => $exam->total_marks,
            ],
            'marks'    => $marks,
        ]);
    }

    // Student → View all marks for a class
    public function myMarks($classId)
    {
        $user = auth('api')->user();

        $exams = Exam::where('class_id', $classId)
            ->orderBy('exam_date', 'asc')
            ->get()
            ->map(function ($exam) use ($user) {

                $mark = Mark::where('exam_id', $exam->id)
                    ->where('student_id', $user->id)
                    ->first();

                return [
                    'exam_id'       => $exam->id,
                    'title'         => $exam->title,
                    'exam_type'     => $exam->exam_type,
                    'exam_date'     => $exam->exam_date,
                    'total_marks'   => $exam->total_marks,
                    'marks_obtained' => $mark->marks_obtained ?? null,
                    'grade'         => $mark->grade ?? null,
                ];
            });

        return response()->json([
            'message' => 'My marks fetched successfully.',
            'data'    => $exams,
        ]);
    }
}