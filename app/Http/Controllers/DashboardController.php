<?php

namespace App\Http\Controllers;

use App\Models\Classes;
use App\Models\Assignment;
use App\Models\Exam;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function summary(Request $request)
    {
        // API গার্ড থেকে ইউজার নাও
        $user = Auth::guard('api')->user() ?? $request->user('api');

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $roleName = optional($user->role)->role_name ?? ($user->role_name ?? 'User');
        $roleSlug = strtolower($roleName);

        $isTeacherLike = in_array($roleSlug, ['teacher', 'admin']);
        $isStudent     = ($roleSlug === 'student');

        // ডিফল্ট ভ্যালু
        $activeClasses      = 0;
        $pendingAssignments = 0;
        $upcomingExams      = 0;
        $unreadMessages     = 0;
        $upcomingDeadlines  = [];
        $recentActivities   = [];

        // আগে থেকেই teacher ও student এর class_ids বের করে রাখি
        $classIdsForTeacher = collect();
        $classIdsForStudent = collect();

        // Student এর submitted assignment ids
        $submittedAssignmentIds = collect();

        try {
            // Teacher বা Admin হলে নিজের টিচ করা ক্লাসগুলোর id
            if ($isTeacherLike && Schema::hasTable('classes') && Schema::hasColumn('classes', 'teacher_id')) {
                $classIdsForTeacher = Classes::query()
                    ->where('teacher_id', $user->id)
                    ->pluck('id');
            }

            // Student হলে Enrollments টেবিল থেকে ক্লাস id
            if ($isStudent && Schema::hasTable('enrollments')) {
                // এনরোলমেন্ট স্ক্রিপ্টে কলাম নাম user_id
                $classIdsForStudent = DB::table('enrollments')
                    ->where('user_id', $user->id)
                    ->pluck('class_id');
            }

            // Student এর submitted assignments
            if ($isStudent && Schema::hasTable('submissions')) {
                $submittedAssignmentIds = DB::table('submissions')
                    ->where('student_id', $user->id)
                    ->pluck('assignment_id');
            }
        } catch (\Throwable $e) {
            Log::warning('Dashboard base ids fetch error: ' . $e->getMessage());
        }

        // ১) Active classes
        try {
            if ($isTeacherLike && $classIdsForTeacher->isNotEmpty()) {
                $activeClasses = (int) Classes::query()
                    ->whereIn('id', $classIdsForTeacher)
                    ->where('is_active', true)
                    ->count();
            } elseif ($isStudent && $classIdsForStudent->isNotEmpty()) {
                $activeClasses = (int) Classes::query()
                    ->whereIn('id', $classIdsForStudent)
                    ->where('is_active', true)
                    ->count();
            } else {
                $activeClasses = 0;
            }
        } catch (\Throwable $e) {
            Log::warning('Dashboard active_classes error: ' . $e->getMessage());
            $activeClasses = 0;
        }

        // ২) Pending assignments
        // Student এর ক্ষেত্রে নিজের submitted assignment বাদ
        try {
            if (
                class_exists(Assignment::class)
                && Schema::hasTable('assignments')
                && Schema::hasColumn('assignments', 'class_id')
            ) {
                $now = Carbon::now();

                $assignQuery = Assignment::query()
                    ->where('deadline', '>=', $now);

                if ($isTeacherLike && $classIdsForTeacher->isNotEmpty()) {
                    $assignQuery->whereIn('class_id', $classIdsForTeacher);
                } elseif ($isStudent && $classIdsForStudent->isNotEmpty()) {
                    $assignQuery->whereIn('class_id', $classIdsForStudent);

                    if ($submittedAssignmentIds->isNotEmpty()) {
                        // Student যেগুলো already submit করে ফেলেছে সেগুলো বাদ
                        $assignQuery->whereNotIn('id', $submittedAssignmentIds);
                    }
                } else {
                    $assignQuery->whereRaw('1 = 0');
                }

                $pendingAssignments = (int) $assignQuery->count();
            }
        } catch (\Throwable $e) {
            Log::warning('Dashboard pending_assignments error: ' . $e->getMessage());
            $pendingAssignments = 0;
        }

        // ৩) Upcoming exams (next ৭ days, পুরোনো exam বাদ)
        try {
            if (
                class_exists(Exam::class)
                && Schema::hasTable('exams')
                && Schema::hasColumn('exams', 'class_id')
                && Schema::hasColumn('exams', 'exam_date')
            ) {
                $today    = Carbon::today();
                $nextWeek = $today->copy()->addDays(7);

                $examQuery = Exam::query()
                    ->whereBetween('exam_date', [$today, $nextWeek]);

                if ($isTeacherLike && $classIdsForTeacher->isNotEmpty()) {
                    $examQuery->whereIn('class_id', $classIdsForTeacher);
                } elseif ($isStudent && $classIdsForStudent->isNotEmpty()) {
                    $examQuery->whereIn('class_id', $classIdsForStudent);
                } else {
                    $examQuery->whereRaw('1 = 0');
                }

                $upcomingExams = (int) $examQuery->count();
            }
        } catch (\Throwable $e) {
            Log::warning('Dashboard upcoming_exams error: ' . $e->getMessage());
            $upcomingExams = 0;
        }

        // ৪) Unread messages
        try {
            $unreadMessages = 0;

            if (class_exists(Message::class) && Schema::hasTable('messages')) {
                if (
                    Schema::hasColumn('messages', 'recipient_id')
                    && Schema::hasColumn('messages', 'read_at')
                ) {
                    $unreadMessages = (int) Message::query()
                        ->where('recipient_id', $user->id)
                        ->whereNull('read_at')
                        ->count();
                }
                // অন্য কোনো structure থাকলে আপাতত 0 রাখব
            }
        } catch (\Throwable $e) {
            Log::warning('Dashboard unread_messages error: ' . $e->getMessage());
            $unreadMessages = 0;
        }

        // ৫) Upcoming deadlines
        // Student এর ক্ষেত্রে submitted assignment বাদ
        try {
            if (
                Schema::hasTable('assignments')
                && Schema::hasTable('classes')
                && Schema::hasColumn('assignments', 'class_id')
                && Schema::hasColumn('assignments', 'deadline')
            ) {
                $now = Carbon::now();

                $upcomingQuery = DB::table('assignments')
                    ->join('classes', 'assignments.class_id', '=', 'classes.id')
                    ->select(
                        'assignments.id',
                        'assignments.title',
                        'assignments.deadline',
                        'assignments.class_id',
                        'classes.name as course_name'
                    )
                    ->whereNotNull('assignments.deadline')
                    ->where('assignments.deadline', '>=', $now)
                    ->orderBy('assignments.deadline', 'asc');

                if ($isTeacherLike && $classIdsForTeacher->isNotEmpty()) {
                    $upcomingQuery->whereIn('assignments.class_id', $classIdsForTeacher);
                } elseif ($isStudent && $classIdsForStudent->isNotEmpty()) {
                    $upcomingQuery->whereIn('assignments.class_id', $classIdsForStudent);

                    if ($submittedAssignmentIds->isNotEmpty()) {
                        $upcomingQuery->whereNotIn('assignments.id', $submittedAssignmentIds);
                    }
                } else {
                    $upcomingQuery->whereRaw('1 = 0');
                }

                $upcoming = $upcomingQuery
                    ->limit(5)
                    ->get();

                foreach ($upcoming as $a) {
                    $upcomingDeadlines[] = [
                        'title'     => $a->title ?? 'Assignment',
                        'course'    => $a->course_name ?? 'Class',
                        'due_label' => $a->deadline
                            ? Carbon::parse($a->deadline)->diffForHumans()
                            : 'Due soon',
                        'type'      => 'Assignment',
                    ];
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Dashboard upcomingDeadlines error: ' . $e->getMessage());
            $upcomingDeadlines = [];
        }

        // ৬) Recent activities
        // শিক্ষক আর স্টুডেন্ট দুইজনের জন্যই RBAC ফিল্টার
        try {
            if (
                Schema::hasTable('assignments')
                && Schema::hasTable('classes')
                && Schema::hasColumn('assignments', 'class_id')
            ) {
                $recentQuery = DB::table('assignments')
                    ->join('classes', 'assignments.class_id', '=', 'classes.id')
                    ->select(
                        'assignments.id',
                        'assignments.title',
                        'assignments.created_at',
                        'assignments.class_id',
                        'classes.name as course_name'
                    )
                    ->orderBy('assignments.created_at', 'desc');

                if ($isTeacherLike && $classIdsForTeacher->isNotEmpty()) {
                    $recentQuery->whereIn('assignments.class_id', $classIdsForTeacher);
                } elseif ($isStudent && $classIdsForStudent->isNotEmpty()) {
                    $recentQuery->whereIn('assignments.class_id', $classIdsForStudent);
                } else {
                    $recentQuery->whereRaw('1 = 0');
                }

                $recent = $recentQuery
                    ->limit(5)
                    ->get();

                foreach ($recent as $a) {
                    $recentActivities[] = [
                        'action'     => 'New assignment: ' . ($a->title ?? 'Untitled'),
                        'course'     => $a->course_name ?? 'Class',
                        'time_label' => $a->created_at
                            ? Carbon::parse($a->created_at)->diffForHumans()
                            : null,
                        'type'       => 'Assignment',
                    ];
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Dashboard recentActivities error: ' . $e->getMessage());
            $recentActivities = [];
        }

        return response()->json([
            'message' => 'Dashboard data loaded.',
            'stats' => [
                'active_classes'      => $activeClasses,
                'pending_assignments' => $pendingAssignments,
                'upcoming_exams'      => $upcomingExams,
                'unread_messages'     => $unreadMessages,
            ],
            'upcoming_deadlines' => $upcomingDeadlines,
            'recent_activities'  => $recentActivities,
            'role'               => $roleName,
        ]);
    }
}