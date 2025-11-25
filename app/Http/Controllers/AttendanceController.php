<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Classes;
use App\Models\Enrollment;

class AttendanceController extends Controller
{
    /**
     * Teacher/Admin → Mark Attendance for a class
     */
    public function mark(Request $request, $classId)
    {
        $user = auth('api')->user();
        $class = Classes::findOrFail($classId);

        // Teacher হলে তার নিজের ক্লাস হতে হবে
        if ($user->role->role_name === 'Teacher' && $class->teacher_id !== $user->id) {
            return response()->json(['message' => 'You can only mark attendance for your own class.'], 403);
        }

        $validated = $request->validate([
            'date' => 'required|date',
            'students' => 'required|array',
        ]);

        // students = [ { "student_id": 5, "status": "present" }, ... ]
        foreach ($validated['students'] as $row) {
            Attendance::updateOrCreate(
                [
                    'class_id'  => $class->id,
                    'student_id' => $row['student_id'],
                    'date'      => $validated['date'],
                ],
                [
                    'status'    => $row['status'], // present/absent
                    'marked_by' => $user->id,
                ]
            );
        }

        return response()->json([
            'message' => 'Attendance marked successfully.',
        ]);
    }

    /**
     * Student → View own attendance for a class
     */
    public function myAttendance(Request $request, $classId)
    {
        $user = auth('api')->user();

        $records = Attendance::where('class_id', $classId)
            ->where('student_id', $user->id)
            ->orderBy('date', 'asc')
            ->get();

        return response()->json([
            'message' => 'Attendance fetched.',
            'data'    => $records,
        ]);
    }

    /**
     * Teacher/Admin → Class attendance list
     */
    public function classAttendance($classId)
    {
        $user = auth('api')->user();
        $class = Classes::findOrFail($classId);

        if ($user->role->role_name === 'Teacher' && $class->teacher_id !== $user->id) {
            return response()->json(['message' => 'Access denied.'], 403);
        }

        $attendance = Attendance::where('class_id', $classId)
            ->orderBy('date', 'asc')
            ->get();

        return response()->json([
            'message' => 'Class attendance list loaded.',
            'data'    => $attendance,
        ]);
    }
}