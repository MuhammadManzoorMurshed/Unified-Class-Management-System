<?php

namespace App\Http\Controllers;

use App\Models\Classes;
use App\Models\Exam;
use Illuminate\Http\Request;

class ExamController extends Controller
{
    public function index($classId)
    {
        $class = Classes::findOrFail($classId);

        $exams = Exam::where('class_id', $classId)
            ->orderBy('exam_date', 'asc')
            ->get();

        return response()->json([
            'message' => 'Exams fetched successfully.',
            'data'    => $exams,
        ]);
    }

    public function store(Request $request, $classId)
    {
        $user = auth('api')->user();
        $class = Classes::findOrFail($classId);

        if ($user->role->role_name === 'Teacher' && $class->teacher_id !== $user->id) {
            return response()->json([
                'message' => 'You can only create exams for your own classes.'
            ], 403);
        }

        $validated = $request->validate([
            'exam_type'   => 'required|in:CT,Midterm,Final,Quiz,Viva,Lab Performance,Presentation',
            'title'       => 'required|string|max:150',
            'exam_date'   => 'required|date',
            'total_marks' => 'required|numeric|min:0|max:999.99',
            'weightage'   => 'nullable|numeric|min:0|max:9.99',
            'is_published' => 'boolean',
        ]);

        $exam = Exam::create([
            'class_id'    => $classId,
            'exam_type'   => $validated['exam_type'],
            'title'       => $validated['title'],
            'exam_date'   => $validated['exam_date'],
            'total_marks' => $validated['total_marks'],
            'weightage'   => $validated['weightage'] ?? 1.0,
            'is_published' => $validated['is_published'] ?? false,
        ]);

        return response()->json([
            'message' => 'Exam created successfully.',
            'data'    => $exam,
        ], 201);
    }
}