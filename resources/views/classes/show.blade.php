@extends('layouts.app')

@section('title', 'Class Workspace')
@section('page-id', 'classes.show')
@section('header-title', 'Class Workspace')
@section('header-subtitle', 'Overview')

@section('content')
@php
// Route param fallback: supports both /classes/{id} and /classes/{class}
$classId = request()->route('id') ?? request()->route('class') ?? null;
@endphp

<div class="max-w-7xl mx-auto px-4 py-6 space-y-6" id="class-page" data-class-id="{{ $classId }}">

    {{-- Breadcrumb --}}
    <div class="text-xs text-slate-500 mb-1">
        <a href="{{ route('classes.index') }}" class="hover:underline">My Classes</a>
        <span class="mx-1">/</span>
        <span id="class-breadcrumb-current">Class</span>
    </div>

    {{-- Class Header --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
            {{-- Class Info --}}
            <div class="flex-1">
                <div class="flex items-start gap-4">
                    <div id="class-avatar"
                        class="w-16 h-16 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl flex items-center justify-center text-white font-bold text-lg shadow-sm">
                        CS
                    </div>
                    <div class="flex-1 min-w-0">
                        <h1 id="class-name" class="text-2xl font-bold text-slate-800 mb-2">Loading class...</h1>
                        <div class="flex flex-wrap items-center gap-4 text-sm text-slate-600">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                                </svg>
                                <span id="class-code">–</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                <span id="class-teacher">–</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                <span id="class-member-count">0</span> members
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="flex items-center gap-3">
                <button
                    class="px-4 py-2 bg-white border border-slate-300 rounded-xl text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                    </svg>
                    Share
                </button>
                <button
                    class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm font-semibold hover:bg-indigo-700 transition-colors flex items-center gap-2 shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    New Post
                </button>
            </div>
        </div>
    </div>

    {{-- Main Content Area --}}
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        {{-- Left Sidebar - Quick Stats --}}
        <div class="lg:col-span-1 space-y-6">
            {{-- Class Details Card --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
                <h3 class="font-semibold text-slate-800 mb-4">Class Details</h3>
                <div class="space-y-4 text-sm">
                    <div>
                        <div class="text-slate-500 text-xs font-medium mb-1">Dept.</div>
                        <div id="class-subject" class="font-semibold text-slate-800">–</div>
                    </div>
                    <div>
                        <div class="text-slate-500 text-xs font-medium mb-1">Session</div>
                        <div id="class-semester" class="font-semibold text-slate-800">–</div>
                    </div>
                    <div>
                        <div class="text-slate-500 text-xs font-medium mb-1">Academic Year</div>
                        <div id="class-year" class="font-semibold text-slate-800">–</div>
                    </div>
                    <div>
                        <div class="text-slate-500 text-xs font-medium mb-1">Status</div>
                        <span class="px-2 py-1 bg-emerald-100 text-emerald-700 rounded-lg text-xs font-medium">
                            Active
                        </span>
                    </div>
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
                <h3 class="font-semibold text-slate-800 mb-4">Quick Actions</h3>
                <div class="space-y-2">
                    <button id="btn-quick-create-assignment" type="button"
                        class="w-full flex items-center gap-3 p-3 text-sm text-slate-700 hover:bg-slate-50 rounded-xl transition-colors">
                        <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        Create Assignment
                    </button>
                    <button
                        class="w-full flex items-center gap-3 p-3 text-sm text-slate-700 hover:bg-slate-50 rounded-xl transition-colors">
                        <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                            </svg>
                        </div>
                        Post Announcement
                    </button>
                    <button
                        class="w-full flex items-center gap-3 p-3 text-sm text-slate-700 hover:bg-slate-50 rounded-xl transition-colors">
                        <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                        Upload Files
                    </button>
                </div>
            </div>
        </div>

        {{-- Main Content --}}
        <div class="lg:col-span-3 space-y-6">
            {{-- Tabs Navigation --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200">
                <div class="flex flex-wrap gap-1 p-2 border-b border-slate-100">
                    <button
                        class="tab-btn px-4 py-3 bg-indigo-600 text-white rounded-xl text-sm font-semibold transition-colors flex items-center gap-2"
                        data-tab="posts">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                        </svg>
                        Chats
                    </button>
                    <button
                        class="tab-btn px-4 py-3 text-slate-600 hover:text-slate-800 hover:bg-slate-50 rounded-xl text-sm font-medium transition-colors flex items-center gap-2"
                        data-tab="attendance">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Attendance
                    </button>
                    <button
                        class="tab-btn px-4 py-3 text-slate-600 hover:text-slate-800 hover:bg-slate-50 rounded-xl text-sm font-medium transition-colors flex items-center gap-2"
                        data-tab="assignments">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Assignments
                    </button>
                    <button
                        class="tab-btn px-4 py-3 text-slate-600 hover:text-slate-800 hover:bg-slate-50 rounded-xl text-sm font-medium transition-colors flex items-center gap-2"
                        data-tab="exams">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        Exams & Marks
                    </button>
                    <button
                        class="tab-btn px-4 py-3 text-slate-600 hover:text-slate-800 hover:bg-slate-50 rounded-xl text-sm font-medium transition-colors flex items-center gap-2"
                        data-tab="files">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        Files
                    </button>
                    <button
                        class="tab-btn px-4 py-3 text-slate-600 hover:text-slate-800 hover:bg-slate-50 rounded-xl text-sm font-medium transition-colors flex items-center gap-2"
                        data-tab="members">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        Members
                    </button>
                    <button
                        class="tab-btn px-4 py-3 text-slate-600 hover:text-slate-800 hover:bg-slate-50 rounded-xl text-sm font-medium transition-colors flex items-center gap-2"
                        data-tab="overview">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        Overview
                    </button>
                </div>

                {{-- Tab Content --}}
                <div class="p-6">
                    {{-- Overview Tab Content --}}
                    <div id="tab-content-overview" class="space-y-6">
                        {{-- Description --}}
                        <div>
                            <h3 class="font-semibold text-slate-800 mb-3">Class Description</h3>
                            <p id="class-description" class="text-slate-600 leading-relaxed">–</p>
                        </div>

                        {{-- Stats Grid --}}
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div class="bg-slate-50 rounded-xl p-4 text-center">
                                <div class="text-2xl font-bold text-slate-800 mb-1" id="stats-assignments">0</div>
                                <div class="text-xs text-slate-500">Assignments</div>
                            </div>
                            <div class="bg-slate-50 rounded-xl p-4 text-center">
                                <div class="text-2xl font-bold text-slate-800 mb-1" id="stats-posts">0</div>
                                <div class="text-xs text-slate-500">Posts</div>
                            </div>
                            <div class="bg-slate-50 rounded-xl p-4 text-center">
                                <div class="text-2xl font-bold text-slate-800 mb-1" id="stats-files">0</div>
                                <div class="text-xs text-slate-500">Files</div>
                            </div>
                            <div class="bg-slate-50 rounded-xl p-4 text-center">
                                <div class="text-2xl font-bold text-slate-800 mb-1" id="stats-events">0</div>
                                <div class="text-xs text-slate-500">Events</div>
                            </div>
                        </div>

                        {{-- Recent Activity --}}
                        <div>
                            <h3 class="font-semibold text-slate-800 mb-3">Recent Activity</h3>
                            <div class="space-y-3">
                                <div class="text-center py-8 text-slate-400">
                                    <p class="text-sm">No recent activity</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Posts / Chats Tab --}}
                    <div id="tab-content-posts" class="hidden space-y-4 text-sm text-slate-600">
                        <div class="flex items-center justify-between">
                            <!-- <div>
                                <h3 class="font-semibold text-slate-800">Class Chats</h3>
                                <p class="text-[11px] text-slate-500">
                                    Real-time style discussion space for this class.
                                </p>
                            </div> -->
                        </div>

                        <div
                            class="flex flex-col h-96  bg-slate-50 rounded-2xl border border-slate-200 overflow-hidden">
                            {{-- Messages area --}}
                            <div id="chat-messages-wrapper" class="flex-1 overflow-y-auto px-4 py-3 space-y-3">
                                <div id="chat-messages-list"></div>
                            </div>

                            {{-- Typing indicator --}}
                            <div id="chat-typing-indicator" class="hidden px-4 pb-1 text-[11px] text-slate-400">
                                You are typing…
                            </div>

                            {{-- Input box --}}
                            <div class="border-t border-slate-200 p-3 bg-white">
                                <form id="chat-form" class="flex items-center gap-2">
                                    @csrf
                                    <input id="chat-input" type="text" autocomplete="off"
                                        class="flex-1 rounded-xl border border-slate-300 px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                        placeholder="Write a message...">
                                    <button id="chat-send-btn" type="submit"
                                        class="px-4 py-2 rounded-xl bg-indigo-600 text-white text-xs font-semibold hover:bg-indigo-700 disabled:opacity-60">
                                        <span id="chat-send-text">Send</span>
                                    </button>
                                </form>
                                <p id="chat-error" class="hidden mt-1 text-[11px] text-rose-500"></p>
                            </div>
                        </div>
                    </div>



                    {{-- Attendance Tab --}}
                    <div id="tab-content-attendance" class="hidden space-y-4 text-sm text-slate-600">
                        <!-- <h3 class="font-semibold text-slate-800 mb-3">Attendance</h3> -->

                        {{-- এখানে JS থেকে ডায়নামিক কনটেন্ট বসবে --}}
                        <div id="attendance-content" class="space-y-4 text-sm text-slate-600">
                            <p class="text-sm text-slate-500">Loading attendance...</p>
                        </div>
                    </div>

                    {{-- Assignments Tab --}}
                    <div id="tab-content-assignments" class="hidden space-y-4 text-sm text-slate-600">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <!-- <h3 class="font-semibold text-slate-800">Assignments</h3> -->
                                <p class=" text-base font-bold text-indigo-500" id="assignments-subtitle">
                                    View assignments and deadlines for this class.
                                </p>
                            </div>

                            {{-- শুধুমাত্র Teacher/Admin এর জন্য JS থেকে এই বাটনটি দেখানো হবে --}}
                            <button id="btn-open-assignment-modal" type="button"
                                class="hidden px-3 py-2 rounded-xl text-xs font-bold border border-indigo-600 text-indigo-600 hover:bg-indigo-700 hover:text-white shadow-sm flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4" />
                                </svg>
                                New Assignment
                            </button>
                        </div>

                        <div id="assignments-list" class="space-y-3">
                            <p class="text-sm text-slate-500">Loading assignments...</p>
                        </div>
                    </div>

                    {{-- Exams & Marks Tab --}}
                    <div id="tab-content-exams" class="hidden space-y-4">

                        {{-- Header --}}
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <!-- <h3 class="font-semibold text-slate-900 text-sm sm:text-base">Exams & Marks</h3> -->
                                <p class="text-base text-indigo-500 font-bold" id="exams-marks-subtitle">
                                    Create and manage exams for this class ➜
                                </p>
                            </div>

                            {{-- শুধুমাত্র Teacher/Admin এর জন্য JS থেকে এই বাটন visible হবে --}}
                            <button id="btn-open-exam-modal" type="button"
                                class="hidden px-3 py-2 rounded-xl text-xs font-bold border border-indigo-600 text-indigo-600 hover:bg-indigo-700 hover:text-white shadow-sm flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4" />
                                </svg>
                                New Exam
                            </button>
                        </div>

                        {{-- TEACHER / ADMIN PANEL --}}
                        <div id="exams-marks-teacher-panel" class="hidden space-y-4">
                            {{-- Exam selector --}}
                            <div class="w-full flex items-center justify-center">
                                <div class="inline-flex flex-col items-center gap-2">
                                    <label for="exam-selector"
                                        class="text-xs font-medium text-slate-600 tracking-wide uppercase">
                                        Select Exam
                                    </label>
                                    <select id="exam-selector"
                                        class="min-w-[260px] max-w-xs rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                        <option value="" selected disabled>Choose an exam</option>
                                    </select>
                                </div>
                            </div>

                            {{-- Marks entry card --}}
                            <div id="marks-entry-card"
                                class="hidden bg-white rounded-2xl border border-slate-200 shadow-sm">
                                <div
                                    class="border-b border-slate-200 px-4 py-3 flex items-center justify-between gap-3">
                                    <div class="min-w-0">
                                        <p id="marks-entry-exam-title"
                                            class="text-sm font-semibold text-slate-900 truncate">
                                            Selected exam
                                        </p>
                                        <p id="marks-entry-exam-meta" class="text-xs text-slate-500 mt-0.5">
                                            Type • Date • Total marks
                                        </p>
                                    </div>
                                </div>

                                <div class="px-4 pb-4 pt-3 overflow-x-auto">
                                    <div id="marks-entry-table-wrapper" class="min-w-full">
                                        {{-- JS will inject the table here --}}
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- STUDENT PANEL --}}
                        <div id="exams-marks-student-panel" class="hidden space-y-3">
                            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm">
                                <div class="border-b border-slate-200 px-4 py-3">
                                    <p class="text-sm font-semibold text-slate-900">Your Exam Marks</p>
                                    <p class="text-xs text-slate-500 mt-0.5">
                                        Exam-wise marks for this class.
                                    </p>
                                </div>
                                <div class="px-4 pb-4 pt-3 overflow-x-auto">
                                    <div id="student-marks-table-wrapper" class="min-w-full">
                                        {{-- JS will inject the table here --}}
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>




                    {{-- Files Tab --}}
                    <div id="tab-content-files" class="hidden space-y-4 text-sm text-slate-600">
                        <h3 class="font-semibold text-slate-800 mb-3">Files & Resources</h3>
                        <div class="text-slate-500">
                            This section will show uploaded class files and resources.
                        </div>
                    </div>

                    {{-- Members Tab --}}
                    <div id="tab-content-members" class="hidden space-y-4 text-sm text-slate-600">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <h3 class="font-semibold text-slate-800">Members</h3>
                                <p class="text-xs text-slate-500">
                                    View teacher and enrolled students of this class.
                                </p>
                            </div>
                            <div id="members-count" class="text-xs text-slate-500">
                                Loading members...
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            {{-- Teacher Card --}}
                            <div class="md:col-span-1">
                                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4"
                                    id="class-teacher-card">
                                    <p class="text-xs text-slate-500">Loading teacher info...</p>
                                </div>
                            </div>

                            {{-- Students List --}}
                            <div class="md:col-span-2">
                                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4">
                                    <h4 class="text-xs font-semibold text-slate-700 mb-3">Enrolled Students</h4>
                                    <div id="students-list" class="space-y-2 max-h-80 overflow-y-auto">
                                        <p class="text-xs text-slate-500">Loading students...</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                </div>
            </div>
        </div>
    </div>
</div>

{{-- Assignment Create Modal --}}
<div id="assignment-modal" class="fixed inset-0 z-40 hidden items-center justify-center bg-slate-900/40">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 id="assignment-modal-title" class="font-semibold text-slate-800 text-base">
                Create Assignment
            </h3>
            <button type="button" id="assignment-modal-close" class="p-1 rounded-full hover:bg-slate-100">
                <span class="sr-only">Close</span>
                ✕
            </button>
        </div>

        <form id="assignment-form" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">
                    Title <span class="text-red-500">*</span>
                </label>
                <input type="text" name="title" id="assignment_title"
                    class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    placeholder="e.g. Assignment 1: Array Problems">
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">
                    Description <span class="text-red-500">*</span>
                </label>
                <textarea name="description" id="assignment_description" rows="3"
                    class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    placeholder="Short description for students"></textarea>
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">
                    Instructions (optional)
                </label>
                <textarea name="instructions" id="assignment_instructions" rows="2"
                    class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    placeholder="Detailed instructions, if any"></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">
                        Deadline <span class="text-red-500">*</span>
                    </label>
                    <input type="datetime-local" name="deadline" id="assignment_deadline"
                        class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">
                        Max Marks
                    </label>
                    <input type="number" name="max_marks" id="assignment_max_marks"
                        class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        value="100" min="0" max="999.99" step="0.01">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">
                        Type
                    </label>
                    <select name="assignment_type" id="assignment_type"
                        class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="Homework">Homework</option>
                        <option value="Assignment" selected>Assignment</option>
                        <option value="Lab Report">Lab Report</option>
                        <option value="Project Proposal">Project Proposal</option>
                        <option value="Project Report">Project Report</option>
                        <option value="Project">Project</option>
                        <option value="Thesis">Thesis</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">
                        Attachment (optional)
                    </label>
                    <input type="file" name="file" id="assignment_file" class="w-full text-xs text-slate-600">
                    <p class="text-[11px] text-slate-400 mt-1">
                        Max 10MB. PDF, DOCX, etc.
                    </p>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <button type="button" id="assignment-modal-cancel"
                    class="px-3 py-1.5 rounded-xl border border-slate-300 text-xs text-slate-700 hover:bg-slate-50">
                    Cancel
                </button>
                <button type="submit"
                    class="px-4 py-2 rounded-xl bg-indigo-600 text-white text-xs font-semibold hover:bg-indigo-700 shadow-sm">
                    Save Assignment
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Assignment Submission Modal -->
<div id="submit-assignment-modal" class="hidden fixed inset-0 z-50 bg-black/40 items-center justify-center">
    <div class="bg-white p-5 rounded-2xl shadow-xl w-full max-w-md">
        <h3 class="font-semibold text-slate-800 mb-3">Submit Assignment</h3>

        <form id="submit-assignment-form" enctype="multipart/form-data" class="space-y-3">
            <input type="hidden" id="submit_assignment_id">

            <div>
                <label class="text-xs font-medium text-slate-600">Upload File</label>
                <input type="file" id="submit_assignment_file"
                    class="w-full text-xs border border-slate-300 rounded-lg px-2 py-2">
            </div>

            <div class="flex justify-end gap-3">
                <button type="button" id="submit-assignment-cancel"
                    class="px-3 py-1.5 border text-xs rounded-lg">Cancel</button>

                <button type="submit" class="px-4 py-1.5 bg-indigo-600 text-white text-xs rounded-lg">
                    Submit
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Teacher Submission Modal -->
<div id="review-submissions-modal" class="hidden fixed inset-0 z-50 bg-black/40 items-center justify-center">
    <div class="bg-white p-6 rounded-2xl w-full max-w-4xl shadow-xl overflow-y-auto max-h-[90vh]">
        <h3 class="font-semibold text-slate-800 mb-3">Submissions</h3>

        <table class="min-w-full text-xs border border-slate-200 rounded-xl">
            <thead class="bg-slate-100 text-slate-600">
                <tr>
                    <th class="px-3 py-2 text-left">ID</th>
                    <th class="px-3 py-2">Submitted File</th>
                    <th class="px-3 py-2">Submitted Time</th>
                    <th class="px-3 py-2">Marks</th>
                </tr>
            </thead>
            <tbody id="review-submissions-body" class="bg-white"></tbody>
        </table>

        <div class="flex justify-end mt-4">
            <button id="review-submissions-close" class="px-3 py-1.5 border text-xs rounded-lg">
                Close
            </button>
        </div>
    </div>
</div>

{{-- ★★★ Exam Create Modal ★★★ --}}
<div id="exam-modal" class="fixed inset-0 z-40 hidden items-center justify-center bg-slate-900/40">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 id="exam-modal-title" class="font-semibold text-slate-800 text-base">
                Create Exam
            </h3>
            <button type="button" id="exam-modal-close" class="p-1 rounded-full hover:bg-slate-100">
                <span class="sr-only">Close</span>
                ✕
            </button>
        </div>

        <form id="exam-form" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">
                    Exam Title <span class="text-red-500">*</span>
                </label>
                <input type="text" name="title" id="exam_title"
                    class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    placeholder="e.g. Midterm Exam">
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">
                    Exam Type <span class="text-red-500">*</span>
                </label>
                <select name="exam_type" id="exam_type"
                    class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="CT">CT</option>
                    <option value="Midterm">Midterm</option>
                    <option value="Final">Final</option>
                    <option value="Quiz">Quiz</option>
                    <option value="Viva">Viva</option>
                    <option value="Lab Performance">Lab Performance</option>
                </select>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">
                        Exam Date <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="exam_date" id="exam_date"
                        class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">
                        Total Marks <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="total_marks" id="exam_total_marks"
                        class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        min="0" max="999.99" step="0.01" value="100">
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">
                    Description (optional)
                </label>
                <textarea name="description" id="exam_description" rows="2"
                    class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    placeholder="Short note for exam, if needed"></textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <button type="button" id="exam-modal-cancel"
                    class="px-3 py-1.5 rounded-xl border border-slate-300 text-xs text-slate-700 hover:bg-slate-50">
                    Cancel
                </button>
                <button type="submit"
                    class="px-4 py-2 rounded-xl bg-indigo-600 text-white text-xs font-semibold hover:bg-indigo-700 shadow-sm">
                    Save Exam
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

<!-- @push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const classPageEl = document.getElementById('class-page');
    const classIdFromDom = classPageEl && classPageEl.dataset && classPageEl.dataset.classId ?
        Number(classPageEl.dataset.classId) :
        null;

    // Mock class data - will be replaced by real API later
    const mockClassData = {
        id: classIdFromDom,
        name: 'Introduction to Computer Science',
        code: 'CSE-101-ABCD',
        subject: 'Computer Science',
        teacher: 'Dr. Sarah Wilson',
        memberCount: 42,
        semester: 'Fall 2024',
        year: '2024-2025',
        description: 'This course introduces fundamental concepts of computer science and programming. Students will learn problem-solving techniques, algorithm design, and basic data structures. The course covers programming in Python and includes hands-on projects to reinforce theoretical concepts.',
        stats: {
            assignments: 5,
            posts: 12,
            files: 8,
            events: 3
        }
    };

    // Populate class data if elements exist
    const nameEl = document.getElementById('class-name');
    const codeEl = document.getElementById('class-code');
    const teacherEl = document.getElementById('class-teacher');
    const memberCountEl = document.getElementById('class-member-count');
    const subjectEl = document.getElementById('class-subject');
    const semesterEl = document.getElementById('class-semester');
    const yearEl = document.getElementById('class-year');
    const descriptionEl = document.getElementById('class-description');
    const breadcrumbCurrent = document.getElementById('class-breadcrumb-current');
    const avatarEl = document.getElementById('class-avatar');

    if (nameEl) nameEl.textContent = mockClassData.name;
    if (codeEl) codeEl.textContent = mockClassData.code;
    if (teacherEl) teacherEl.textContent = mockClassData.teacher;
    if (memberCountEl) memberCountEl.textContent = mockClassData.memberCount;
    if (subjectEl) subjectEl.textContent = mockClassData.subject;
    if (semesterEl) semesterEl.textContent = mockClassData.semester;
    if (yearEl) yearEl.textContent = mockClassData.year;
    if (descriptionEl) descriptionEl.textContent = mockClassData.description;
    if (breadcrumbCurrent) breadcrumbCurrent.textContent = mockClassData.name;

    if (avatarEl && mockClassData.code) {
        avatarEl.textContent = mockClassData.code.split('-')[0];
    }

    // Populate stats
    const statsAssignmentsEl = document.getElementById('stats-assignments');
    const statsPostsEl = document.getElementById('stats-posts');
    const statsFilesEl = document.getElementById('stats-files');
    const statsEventsEl = document.getElementById('stats-events');

    if (statsAssignmentsEl) statsAssignmentsEl.textContent = mockClassData.stats.assignments;
    if (statsPostsEl) statsPostsEl.textContent = mockClassData.stats.posts;
    if (statsFilesEl) statsFilesEl.textContent = mockClassData.stats.files;
    if (statsEventsEl) statsEventsEl.textContent = mockClassData.stats.events;

    // Tab functionality
    const tabButtons = document.querySelectorAll('.tab-btn');
    const tabContents = {
        overview: document.getElementById('tab-content-overview'),
        posts: document.getElementById('tab-content-posts'),
        assignments: document.getElementById('tab-content-assignments'),
        exams: document.getElementById('tab-content-exams'),
        files: document.getElementById('tab-content-files'),
        members: document.getElementById('tab-content-members')
    };

    function setActiveTab(target) {
        tabButtons.forEach(btn => {
            btn.classList.remove('bg-indigo-600', 'text-white');
            btn.classList.add('text-slate-600', 'hover:text-slate-800', 'hover:bg-slate-50');
        });

        Object.keys(tabContents).forEach(key => {
            const section = tabContents[key];
            if (section) {
                section.classList.add('hidden');
            }
        });

        const activeButton = Array.from(tabButtons).find(btn => btn.dataset.tab === target);
        if (activeButton) {
            activeButton.classList.remove('text-slate-600', 'hover:text-slate-800', 'hover:bg-slate-50');
            activeButton.classList.add('bg-indigo-600', 'text-white');
        }

        if (tabContents[target]) {
            tabContents[target].classList.remove('hidden');
        }
    }

    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const target = this.dataset.tab || 'overview';
            setActiveTab(target);
        });
    });

    // Default active tab
    setActiveTab('overview');
});
</script>
@endpush -->