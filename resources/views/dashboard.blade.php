@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-id', 'dashboard')
@section('header-title', 'PREMIER UNIVERSITY, CHITTAGONG')
@section('header-subtitle', 'A Center Of Execellence For Quality Learning')

@section('content')
@php
/** @var \App\Models\User|null $user */
$user = auth()->user();

// Stats: controller থেকে associative array হিসেবে পাঠাবে
// ['active_classes' => int, 'pending_assignments' => int, 'upcoming_exams' => int, 'unread_messages' => int]
$stats = $stats ?? [];

$activeClasses = $stats['active_classes'] ?? 0;
$pendingAssignments = $stats['pending_assignments'] ?? 0;
$upcomingExams = $stats['upcoming_exams'] ?? 0;
$unreadMessages = $stats['unread_messages'] ?? 0;

// Deadlines: array of ['title','course','due_label','type']
$upcomingDeadlines = $upcomingDeadlines ?? [];

// Activities: array of ['action','course','time_label','type']
$recentActivities = $recentActivities ?? [];

$roleName = optional($user->role ?? null)->role_name ?? 'User';
@endphp

<div class="max-w-7xl mx-auto px-4 py-6 space-y-6">

    {{-- Hero Section --}}
    <div
        class="bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 rounded-2xl p-6 text-white shadow-lg relative overflow-hidden">
        <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -translate-y-16 translate-x-16"></div>
        <div class="absolute bottom-0 left-0 w-24 h-24 bg-white/10 rounded-full translate-y-12 -translate-x-12"></div>

        <div class="relative z-10 py-6">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold mb-2">
                        @if($user)
                        Welcome, <span id="dash-name" class="text-yellow-300">{{ $user->name }}</span>
                        @else
                        Welcome to UCMS
                        @endif
                    </h1>
                    <p class="text-indigo-100 text-sm md:text-base">
                        Overview of your classes and recent activity
                    </p>
                    @if($user)
                    <p class="text-indigo-100 text-xs md:text-sm mt-2">
                        Role:
                        <span id="dash-role" class="font-semibold">{{ $roleName }}</span>
                        @if($user->email)
                        <span class="mx-1">•</span>
                        <span id="dash-email">{{ $user->email }}</span>
                        @endif
                    </p>
                    @endif
                </div>
                <div>
                    <div class="flex items-center space-x-3 bg-white/20 backdrop-blur-sm rounded-xl px-4 py-2">
                        <span class="text-xs uppercase tracking-wide">Today</span>
                        <span class="font-semibold" id="current-date">
                            {{ now()->format('l, F j, Y') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Row 2: Quick Stats Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Active Classes --}}
        <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-l-blue-500 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-slate-500 font-medium">Active classes</p>
                    <p class="text-2xl font-bold mt-2 text-slate-800" id="dash-active-classes">
                        {{ $activeClasses }}
                    </p>
                    <p class="text-xs text-slate-400 mt-1">Current term</p>
                </div>
                <div
                    class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center text-xs font-semibold text-blue-700">
                    CLS
                </div>
            </div>
        </div>

        {{-- Pending Assignments --}}
        <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-l-green-500 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-slate-500 font-medium">Pending assignments</p>
                    <p class="text-2xl font-bold mt-2 text-slate-800" id="dash-due-assignments">
                        {{ $pendingAssignments }}
                    </p>
                    <p class="text-xs text-slate-400 mt-1">Due this week</p>
                </div>
                <div
                    class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center text-xs font-semibold text-green-700">
                    ASN
                </div>
            </div>
        </div>

        {{-- Upcoming Exams --}}
        <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-l-orange-500 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-slate-500 font-medium">Upcoming exams</p>
                    <p class="text-2xl font-bold mt-2 text-slate-800" id="dash-upcoming-exams">
                        {{ $upcomingExams }}
                    </p>
                    <p class="text-xs text-slate-400 mt-1">Next 7 days</p>
                </div>
                <div
                    class="w-10 h-10 bg-orange-100 rounded-xl flex items-center justify-center text-xs font-semibold text-orange-700">
                    EXM
                </div>
            </div>
        </div>

        {{-- Unread Messages --}}
        <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-l-purple-500 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-slate-500 font-medium">Unread messages</p>
                    <p class="text-2xl font-bold mt-2 text-slate-800" id="dash-unread-messages">
                        {{ $unreadMessages }}
                    </p>
                    <p class="text-xs text-slate-400 mt-1">Require attention</p>
                </div>
                <div
                    class="w-10 h-10 bg-purple-100 rounded-xl flex items-center justify-center text-xs font-semibold text-purple-700">
                    MSG
                </div>
            </div>
        </div>
    </div>

    {{-- Row 3: Deadlines + Recent Activity --}}
    <div class="grid lg:grid-cols-2 gap-6">
        {{-- Upcoming Deadlines --}}
        <div class="bg-white rounded-xl shadow-sm p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-slate-800 flex items-center">
                    <span class="w-2 h-2 bg-red-500 rounded-full mr-2"></span>
                    Upcoming deadlines
                </h3>
                <a href="{{ route('classes.index') }}" class="text-xs text-indigo-600 hover:underline font-medium">
                    View all
                </a>
            </div>

            <div class="space-y-3" id="upcoming-deadlines">
                @if(count($upcomingDeadlines))
                @foreach($upcomingDeadlines as $item)
                <div
                    class="flex items-center justify-between p-3 bg-slate-50 rounded-lg border border-slate-200 hover:border-slate-300 transition">
                    <div class="flex items-center space-x-3">
                        <div
                            class="w-8 h-8 rounded-lg bg-slate-200 flex items-center justify-center text-[10px] font-semibold text-slate-700">
                            {{ strtoupper(substr($item['type'] ?? 'task', 0, 3)) }}
                        </div>
                        <div>
                            <div class="text-sm font-medium text-slate-800">
                                {{ $item['title'] ?? 'Untitled item' }}
                            </div>
                            <div class="text-xs text-slate-500">
                                {{ $item['course'] ?? 'Unknown course' }}
                            </div>
                        </div>
                    </div>
                    <div class="text-xs font-medium text-red-600 bg-red-50 px-2 py-1 rounded">
                        {{ $item['due_label'] ?? 'Due soon' }}
                    </div>
                </div>
                @endforeach
                @else
                <div class="text-center py-8 text-slate-400">
                    <p class="text-sm">No upcoming deadlines.</p>
                </div>
                @endif
            </div>
        </div>

        {{-- Recent Activity --}}
        <div class="bg-white rounded-xl shadow-sm p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-slate-800 flex items-center">
                    <span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span>
                    Recent activity
                </h3>
                <a href="#" class="text-xs text-indigo-600 hover:underline font-medium">
                    See all
                </a>
            </div>

            <div class="space-y-3" id="recent-activity">
                @if(count($recentActivities))
                @foreach($recentActivities as $activity)
                <div
                    class="flex items-center space-x-3 p-3 bg-slate-50 rounded-lg border border-slate-200 hover:border-slate-300 transition">
                    <div
                        class="w-8 h-8 rounded-lg bg-slate-200 flex items-center justify-center text-[10px] font-semibold text-slate-700">
                        {{ strtoupper(substr($activity['type'] ?? 'item', 0, 3)) }}
                    </div>
                    <div class="flex-1">
                        <div class="text-sm font-medium text-slate-800">
                            {{ $activity['action'] ?? 'Activity' }}
                        </div>
                        <div class="text-xs text-slate-500">
                            {{ $activity['course'] ?? 'Unknown course' }}
                            @if(!empty($activity['time_label']))
                            • {{ $activity['time_label'] }}
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
                @else
                <div class="text-center py-8 text-slate-400">
                    <p class="text-sm">No recent activity.</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Row 4: Role-based Sections --}}

    {{-- Teacher/Admin Section --}}
    @if(in_array(strtolower($roleName), ['teacher', 'admin']))
    <div id="dash-admin-teacher" class="space-y-6">
        <div class="grid lg:grid-cols-2 gap-6">
            {{-- Class Performance Overview --}}
            <div class="bg-white rounded-xl shadow-sm p-5">
                <h3 class="font-semibold text-slate-800 mb-4 flex items-center">
                    <span class="w-2 h-2 bg-blue-500 rounded-full mr-2"></span>
                    Class performance overview
                </h3>
                <div
                    class="h-48 bg-gradient-to-br from-slate-50 to-slate-100 rounded-lg flex items-center justify-center text-slate-400 border-2 border-dashed border-slate-200">
                    <div class="text-center">
                        <p class="text-sm">Performance charts will appear here.</p>
                    </div>
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="bg-white rounded-xl shadow-sm p-5">
                <h3 class="font-semibold text-slate-800 mb-4 flex items-center">
                    <span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span>
                    Quick actions
                </h3>
                <div class="grid grid-cols-2 gap-3 text-sm">
                    <button
                        class="p-4 bg-indigo-50 rounded-xl text-indigo-700 font-medium hover:bg-indigo-100 transition-all border border-indigo-100">
                        New assignment
                    </button>
                    <button
                        class="p-4 bg-green-50 rounded-xl text-green-700 font-medium hover:bg-green-100 transition-all border border-green-100">
                        Post announcement
                    </button>
                    <button
                        class="p-4 bg-orange-50 rounded-xl text-orange-700 font-medium hover:bg-orange-100 transition-all border border-orange-100">
                        Take attendance
                    </button>
                    <button
                        class="p-4 bg-purple-50 rounded-xl text-purple-700 font-medium hover:bg-purple-100 transition-all border border-purple-100">
                        Create class
                    </button>
                </div>
            </div>
        </div>

        {{-- Recent Submissions --}}
        <div class="bg-white rounded-xl shadow-sm p-5">
            <h3 class="font-semibold text-slate-800 mb-4 flex items-center">
                <span class="w-2 h-2 bg-purple-500 rounded-full mr-2"></span>
                Recent submissions
            </h3>
            <div class="text-center py-8 text-slate-400">
                <p class="text-sm">No recent submissions.</p>
            </div>
        </div>
    </div>
    @endif

    {{-- Student Section --}}
    @if(strtolower($roleName) === 'student')
    <div id="dash-student" class="space-y-6">
        <div class="grid lg:grid-cols-2 gap-6">
            {{-- My Classes Overview --}}
            <div class="bg-white rounded-xl shadow-sm p-5">
                <h3 class="font-semibold text-slate-800 mb-4 flex items-center">
                    <span class="w-2 h-2 bg-blue-500 rounded-full mr-2"></span>
                    My classes
                </h3>
                <div class="space-y-3" id="student-classes-list">
                    <div class="text-center py-8 text-slate-400">
                        <p class="text-sm">No active classes.</p>
                    </div>
                </div>
                <div class="mt-4">
                    <a href="{{ route('classes.index') }}"
                        class="inline-flex items-center text-sm text-indigo-600 hover:text-indigo-700 font-medium">
                        View all classes
                        <span class="ml-1">→</span>
                    </a>
                </div>
            </div>

            {{-- Grades & Attendance --}}
            <div class="bg-white rounded-xl shadow-sm p-5">
                <h3 class="font-semibold text-slate-800 mb-4 flex items-center">
                    <span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span>
                    My performance
                </h3>
                <div
                    class="h-48 bg-gradient-to-br from-slate-50 to-slate-100 rounded-lg flex items-center justify-center text-slate-400 border-2 border-dashed border-slate-200">
                    <div class="text-center">
                        <p class="text-sm">Your marks and attendance summary will appear here.</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Pending Tasks --}}
        <div class="bg-white rounded-xl shadow-sm p-5">
            <h3 class="font-semibold text-slate-800 mb-4 flex items-center">
                <span class="w-2 h-2 bg-orange-500 rounded-full mr-2"></span>
                Pending tasks
            </h3>
            <div class="text-center py-8 text-slate-400">
                <p class="text-sm">No pending tasks.</p>
            </div>
        </div>
    </div>
    @endif

    {{-- Row 5: Assistant Tips --}}
    <div class="bg-gradient-to-r from-cyan-50 to-blue-50 rounded-xl shadow-sm p-5 border border-cyan-100">
        <div class="flex items-start justify-between gap-4">
            <div class="flex-1">
                <h3 class="font-semibold text-slate-800 mb-2 flex items-center">
                    <span class="w-2 h-2 bg-cyan-500 rounded-full mr-2"></span>
                    Assistant tips
                </h3>
                <p class="text-sm text-slate-600 mb-3">Simple suggestions to help you stay on track.</p>
                <div class="grid md:grid-cols-2 gap-3 text-xs">
                    <div class="bg-white/60 rounded-lg p-3 border border-cyan-200">
                        <span class="font-medium text-cyan-700">Tip:</span>
                        <span class="text-slate-600">Review upcoming deadlines from the dashboard at the start of each
                            week.</span>
                    </div>
                    <div class="bg-white/60 rounded-lg p-3 border border-cyan-200">
                        <span class="font-medium text-cyan-700">Reminder:</span>
                        <span class="text-slate-600">Use “My classes” to jump directly into your most active
                            courses.</span>
                    </div>
                </div>
            </div>
            <div
                class="ml-4 w-12 h-12 bg-cyan-100 rounded-xl flex items-center justify-center text-sm font-semibold text-cyan-700">
                AI
            </div>
        </div>
    </div>

</div>
@endsection