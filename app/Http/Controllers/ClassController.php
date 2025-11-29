<?php

namespace App\Http\Controllers;

use App\Models\AuditLogs;
use Illuminate\Http\Request;
use App\Models\Classes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\Enrollment;
use App\Http\Requests\JoinClassRequest;
use App\Http\Resources\ClassResource;

class ClassController extends Controller
{
    public function show($id)
    {
        $class = Classes::with([
            'teacher:id,name,email',
            // enrolled active students + তাদের user
            'activeEnrollments.user:id,name,email'
        ])
            ->withCount(['activeEnrollments as member_count'])
            ->findOrFail($id);

        return new ClassResource($class);
    }

    public function audit($action, $entityId, $meta = null)
    {
        // লগ তৈরি করা হচ্ছে
        AuditLogs::create([
            'action' => $action,         // অ্যাকশন (যেমন 'class.join', 'class.removeMember' ইত্যাদি)
            'entity_type' => 'Class',    // এখানে entity_type হতে পারে 'Class', 'Enrollment' ইত্যাদি
            'entity_id' => $entityId,    // এটি হবে ক্লাস বা এনরোলমেন্টের ID
            'meta' => json_encode($meta), // অতিরিক্ত ডেটা যদি থাকে
            'user_id' => auth('api')->id(), // বর্তমানে লগড-ইন ইউজারের ID
            'ip_address' => request()->ip(), // ইউজারের আইপি অ্যাড্রেস
        ]);
    }
    /**
     * Store a newly created class.
     */
    public function store(Request $request)
    {
        // Authorization via policy (will throw 403 if unauthorized)
        $this->authorize('create', Classes::class);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'subject' => 'required|string|max:255',
            'session' => 'required|string|max:100',
        ]);

        $class = Classes::create([
            'name' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'teacher_id' => auth('api')->id() ?: null,
            // 'code' => Str::slug($validated['title']) . '-' . Str::random(4),
            'code' => 'CSE-' . Str::upper(Str::random(5)),
            'subject' => $validated['subject'],
            'semester' => $validated['session'],
            'year' => date('Y'),
            'is_active' => true,
            'max_students' => 50,
        ]);

        return response()->json([
            'message' => '✅ Class created successfully!',
            'data' => $class,
        ], 201);
    }

    /**
     * Update the specified class.
     */
    public function update(Request $request, $id)
    {
        $class = Classes::findOrFail($id);
        $this->authorize('manage', $class);  // Check if the user can manage the class

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
        ]);

        // Map API `title` to DB `name` when updating
        if (array_key_exists('title', $validated)) {
            $validated['name'] = $validated['title'];
            unset($validated['title']);
        }

        $class->update($validated);

        return response()->json(['message' => '✅ Class updated', 'data' => $class]);
    }

    /**
     * Remove the specified class.
     */
    public function destroy($id)
    {
        $class = Classes::findOrFail($id);
        $this->authorize('manage', $class);  // Check if the user can manage the class

        $class->delete();

        return response()->json(['message' => '✅ Class deleted']);
    }

    public function join(JoinClassRequest $r)
    {
        // ক্লাস কোড দিয়ে ক্লাস খোঁজা এবং নিশ্চিত করা যে এটি অ্যাক্টিভ
        $class = Classes::where('code', $r->code)->where('is_active', 1)->firstOrFail();

        // ক্লাসটি পূর্ণ কি না চেক করা
        $count = Enrollment::where('class_id', $class->id)->where('status', 'active')->count();
        abort_if($count >= $class->max_students, 422, 'Class is full');

        // শিক্ষার্থীকে ক্লাসে যোগদান করা
        Enrollment::updateOrCreate(
            ['user_id' => auth('api')->id(), 'class_id' => $class->id],
            ['status' => 'active']
        );

        // অডিট রেকর্ড তৈরি
        $this->audit('class.join', $class->id, ['by' => auth('api')->id()]);

        return response()->json(['message' => 'Joined successfully']);
    }

    public function removeMember($classId, $userId)
    {
        $user = Auth::guard('api')->user();

        if (! $user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // ❌ এখানে বাগ ছিল
        // $class = Classes::where('code', $classId)->first();

        // ✅ ফিক্সড ভার্সন
        $class = Classes::find($classId);
        if (! $class) {
            return response()->json(['message' => 'Class not found'], 404);
        }

        // শুধু Teacher/Admin পারবে ক্লাস মেম্বার রিমুভ করতে
        $this->authorize('manage', $class);

        // teacher নিজেকে রিমুভ করতে পারবে না
        if ($class->teacher_id == $userId) {
            return response()->json(['message' => 'You cannot remove the class teacher'], 400);
        }

        $enrollment = Enrollment::where('class_id', $class->id)
            ->where('user_id', $userId)
            ->first();

        if (! $enrollment) {
            return response()->json(['message' => 'Member not found in this class'], 404);
        }

        $enrollment->delete();

        return response()->json(['message' => 'Member removed']);
    }


    public function myClasses(Request $request)
    {
        $user = Auth::guard('api')->user();

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $classes = Classes::query()
            ->with('teacher:id,name,email')
            ->withCount(['activeEnrollments as member_count'])
            ->where(function ($q) use ($user) {
                $q->where('teacher_id', $user->id)
                    ->orWhereHas('enrollments', function ($sub) use ($user) {
                        $sub->where('user_id', $user->id)
                            ->where('status', 'active');
                    });
            })
            ->where('is_active', true)
            ->orderByDesc('created_at')
            ->get()
            ->unique('id')
            ->values();

        return ClassResource::collection($classes);
    }
}