@extends('layouts.app')

@section('title', 'My Classes')
@section('page-id', 'classes.index')
@section('header-title', 'PREMIER UNIVERSITY, CHITTAGONG')
@section('header-subtitle', 'A Center of Excellence For Quality Learning')

@section('content')
@php
/** @var \Illuminate\Support\Collection|\App\Models\Classroom[]|array $classes */
$classes = $classes ?? [];

/** @var \App\Models\User|null $user */
$user = auth()->user();
$roleName = optional($user->role ?? null)->role_name ?? 'User';

// Controller থেকে যাই আসুক, JS-friendly array বানিয়ে পাঠাচ্ছি
$normalizedClasses = collect($classes)->map(function ($c) {
$arr = is_array($c) ? $c : $c->toArray();

return [
'id' => $arr['id'] ?? null,
'name' => $arr['name'] ?? ($arr['class_name'] ?? 'Untitled class'),
'code' => $arr['code'] ?? ($arr['class_code'] ?? '—'),
'description' => $arr['description'] ?? 'No description provided.',
'teacher' => $arr['teacher_name'] ?? ($arr['teacher']['name'] ?? 'Unknown teacher'),
'member_count' => $arr['member_count'] ?? ($arr['members_count'] ?? 0),
'assignments' => $arr['assignments_count'] ?? 0,
'semester' => $arr['semester'] ?? 'N/A',
'is_archived' => (bool)($arr['is_archived'] ?? ($arr['archived'] ?? false)),
];
})->values();
@endphp

{{-- JS-এর জন্য safe data container (এখানে @json, script-এর বাইরে) --}}
<div id="ucms-classes-data" data-class-show-base="{{ url('classes') }}"
    data-initial-classes='@json($normalizedClasses)'>
</div>

<div class="max-w-7xl mx-auto px-4 py-6 space-y-6">

    {{-- Quick Actions Bar --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">My Classes</h1>
            <p class="text-sm text-slate-500 mt-1">Manage and join your academic classes</p>
        </div>
        <div class="flex items-center gap-3">
            <button type="button" id="btn-create-class"
                class="hidden px-4 py-2 bg-white border border-slate-300 rounded-xl text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Create Class
            </button>
        </div>
    </div>

    <!-- {{-- Create Class Button --}}
    <button type="button" id="btn-create-class"
        class="px-4 py-2 bg-white border border-slate-300 rounded-xl text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors flex items-center gap-2">
        ...
    </button> -->

    <!-- {{-- Modal --}}
    <div id="create-class-modal" class="fixed inset-0 bg-black/40 hidden items-center justify-center z-50">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
            <h2 class="text-lg font-semibold text-slate-800 mb-4">Create Class</h2>
            <form id="create-class-form" class="space-y-4">
                <div>
                    <label class="block text-xs font-medium mb-1">Title</label>
                    <input name="title" class="w-full border rounded-lg px-3 py-2 text-sm" required>
                </div>
                <div>
                    <label class="block text-xs font-medium mb-1">Subject</label>
                    <input name="subject" class="w-full border rounded-lg px-3 py-2 text-sm" required>
                </div>
                <div>
                    <label class="block text-xs font-medium mb-1">Semester</label>
                    <input name="semester" class="w-full border rounded-lg px-3 py-2 text-sm"
                        placeholder="Spring / Fall" required>
                </div>
                <div>
                    <label class="block text-xs font-medium mb-1">Description</label>
                    <textarea name="description" rows="3" class="w-full border rounded-lg px-3 py-2 text-sm"></textarea>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" id="btn-cancel-create-class"
                        class="px-3 py-2 text-sm rounded-lg border border-slate-300 text-slate-700">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-4 py-2 text-sm rounded-lg bg-indigo-600 text-white font-semibold hover:bg-indigo-700">
                        Create
                    </button>
                </div>
            </form>
        </div>
    </div> -->


    {{-- Join Class Card --}}
    <div class="bg-gradient-to-r from-indigo-50 to-purple-50 rounded-2xl shadow-sm border border-indigo-100 p-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div class="flex-1">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-10 h-10 bg-indigo-100 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-slate-800">Join a Class</h3>
                        <p class="text-sm text-slate-600">Enter the class code shared by your teacher</p>
                    </div>
                </div>
            </div>
            <form id="join-class-form" class="flex gap-3 w-full lg:w-auto">
                <div class="flex-1 lg:flex-none">
                    <input type="text" name="code"
                        class="w-full lg:w-64 border border-slate-300 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                        placeholder="e.g. CSE-101-ABCD" required>
                </div>
                <button type="submit"
                    class="px-6 py-3 bg-indigo-600 text-white rounded-xl text-sm font-semibold hover:bg-indigo-700 transition-colors shadow-sm">
                    Join Class
                </button>
            </form>
        </div>
    </div>

    {{-- Create Class Modal --}}
    <div id="create-class-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4 p-6 relative">
            {{-- Header --}}
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h2 class="text-lg font-semibold text-slate-800">Create New Class</h2>
                    <p class="text-xs text-slate-500 mt-1">
                        Only Admins and Teachers can create classes.
                    </p>
                </div>
                <button type="button" id="create-class-close" class="text-slate-400 hover:text-slate-600">
                    ✕
                </button>
            </div>

            {{-- Error box --}}
            <div id="create-class-error"
                class="hidden mb-3 text-xs text-red-500 bg-red-50 border border-red-100 rounded-lg px-3 py-2">
            </div>

            {{-- Form --}}
            <form id="create-class-form" class="space-y-4">
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">
                        Class Title
                    </label>
                    <input type="text" name="title"
                        class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                        placeholder="e.g. Algorithm Design & Analysis" required>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">
                        Subject
                    </label>
                    <input type="text" name="subject"
                        class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                        placeholder="e.g. Computer Science & Engineering" required>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">
                        Session / Semester
                    </label>
                    <input type="text" name="session"
                        class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                        placeholder="e.g. Spring" required>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">
                        Description (optional)
                    </label>
                    <textarea name="description" rows="3"
                        class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                        placeholder="Short overview of this class..."></textarea>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" id="create-class-cancel"
                        class="px-4 py-2 text-xs font-medium text-slate-600 hover:bg-slate-100 rounded-lg">
                        Cancel
                    </button>
                    <button type="submit" id="create-class-submit"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-xs font-semibold hover:bg-indigo-700 flex items-center gap-2">
                        <span id="create-class-submit-text">Create</span>
                        <span id="create-class-submit-spinner" class="hidden text-[10px]">
                            Saving...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Classes Grid Section --}}
    <div class="space-y-6">

        {{-- Header with Enhanced Filters --}}
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 p-1 mb-3">
            <div class="space-y-1">
                <h2 class="text-2xl font-bold text-slate-800">Active Classes</h2>
                <p class="text-sm text-slate-500" id="classes-count">
                    <span class="font-semibold text-slate-700">0</span> classes in your list
                </p>
            </div>

            <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                {{-- Enhanced Filter Bar --}}
                <div class="flex items-center gap-1 bg-slate-100/80 rounded-2xl p-1.5 backdrop-blur-sm">
                    <button type="button"
                        class="classes-filter px-4 py-2.5 rounded-xl bg-white text-slate-800 font-semibold text-sm shadow-sm border border-slate-200 transition-all duration-200 hover:shadow-md"
                        data-filter="all">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                            </svg>
                            All Classes
                        </span>
                    </button>
                    <button type="button"
                        class="classes-filter px-4 py-2.5 rounded-xl text-slate-600 font-medium text-sm transition-all duration-200 hover:bg-white hover:text-slate-800 hover:shadow-sm"
                        data-filter="active">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Current
                        </span>
                    </button>
                    <button type="button"
                        class="classes-filter px-4 py-2.5 rounded-xl text-slate-600 font-medium text-sm transition-all duration-200 hover:bg-white hover:text-slate-800 hover:shadow-sm"
                        data-filter="archived">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                            </svg>
                            Archived
                        </span>
                    </button>
                </div>

                {{-- View Toggle (UI only, এখনো functional না) --}}
                <div class="flex items-center gap-1 bg-slate-100/80 rounded-2xl p-1.5 backdrop-blur-sm">
                    <button
                        class="p-2.5 text-slate-600 hover:text-slate-800 hover:bg-white rounded-xl transition-all duration-200 hover:shadow-sm"
                        type="button" title="Grid View">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                        </svg>
                    </button>
                    <button
                        class="p-2.5 text-slate-600 hover:text-slate-800 hover:bg-white rounded-xl transition-all duration-200 hover:shadow-sm"
                        type="button" title="List View">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        {{-- Empty State --}}
        <div id="classes-empty"
            class="bg-gradient-to-br from-slate-50 to-blue-50/30 rounded-3xl shadow-sm border border-slate-200/60 p-16 text-center">
            <div class="max-w-md mx-auto">
                <div
                    class="w-20 h-20 bg-gradient-to-br from-indigo-100 to-purple-100 rounded-3xl flex items-center justify-center mx-auto mb-6 shadow-inner">
                    <svg class="w-10 h-10 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-slate-800 mb-3">No classes yet</h3>
                <p class="text-slate-600 mb-8 leading-relaxed">
                    Get started by joining a class with an access code or create your first class to begin organizing
                    your coursework.
                </p>
                <div class="flex flex-col sm:flex-row gap-3 justify-center">
                    <button
                        class="px-6 py-3 bg-indigo-600 text-white rounded-xl text-sm font-semibold hover:bg-indigo-700 transition-all duration-200 shadow-sm hover:shadow-md flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Create New Class
                    </button>
                    <button
                        class="px-6 py-3 bg-white text-slate-700 rounded-xl text-sm font-semibold border border-slate-300 hover:border-slate-400 transition-all duration-200 shadow-sm hover:shadow-md flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                        </svg>
                        Join with Code
                    </button>
                </div>
            </div>
        </div>

        {{-- Dynamic Classes Grid --}}
        <div id="classes-list" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6" hidden>
            {{-- Cards rendered by JS --}}
        </div>
    </div>
</div>
@endsection

<!-- @push('scripts')
<script>
// Blade থেকে data-attribute দিয়ে আসা মান JS-এ তোলা
(function() {
    const dataEl = document.getElementById('ucms-classes-data');
    window.ucms = window.ucms || {};

    if (dataEl) {
        window.ucms.classShowBase = dataEl.dataset.classShowBase || '';
        try {
            window.ucms.initialClasses = JSON.parse(dataEl.dataset.initialClasses || '[]');
        } catch (e) {
            console.error('Failed to parse initial classes JSON', e);
            window.ucms.initialClasses = [];
        }
    } else {
        window.ucms.classShowBase = "{{ url('classes') }}";
        window.ucms.initialClasses = [];
    }
})();

function normalizeClass(raw) {
    return {
        id: raw.id,
        name: raw.name || 'Untitled class',
        code: raw.code || '—',
        description: raw.description || 'No description provided.',
        teacher: raw.teacher || 'Unknown teacher',
        memberCount: Number(raw.member_count ?? 0),
        assignments: Number(raw.assignments ?? 0),
        semester: raw.semester || 'N/A',
        isArchived: Boolean(raw.is_archived ?? false),
    };
}

// Modern class card UI
function renderClassCard(classData) {
    const codeLabel = (classData.code || 'CLS').substring(0, 3).toUpperCase();

    return `
        <div
            class="group bg-white rounded-2xl shadow-sm border border-slate-200/70
                   hover:shadow-lg hover:border-slate-300 transition-all duration-300
                   cursor-pointer overflow-hidden"
            onclick="window.location.href='${window.ucms.classShowBase}/${classData.id}'">

            <div class="h-1.5 w-full bg-gradient-to-r from-indigo-500 to-purple-600"></div>

            <div class="p-6 space-y-5">

                <div class="flex items-start justify-between">
                    <div
                        class="w-12 h-12 rounded-xl bg-indigo-50 border border-indigo-200
                               flex items-center justify-center font-bold text-indigo-600 text-sm">
                        ${codeLabel}
                    </div>

                    <span
                        class="px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100
                               text-emerald-700 flex items-center gap-1">
                        <span class="w-2 h-2 bg-emerald-500 rounded-full"></span>
                        ${classData.isArchived ? 'Archived' : 'Active'}
                    </span>
                </div>

                <div class="space-y-2">
                    <h3 class="font-bold text-slate-800 text-lg leading-snug
                               group-hover:text-indigo-600 transition-colors line-clamp-2">
                        ${classData.name}
                    </h3>

                    <p class="text-slate-600 text-sm leading-relaxed line-clamp-2">
                        ${classData.description}
                    </p>

                    <div class="flex items-center gap-2 text-xs text-slate-500 mt-1">
                        <span class="font-mono bg-slate-100 px-2 py-1 rounded-lg">
                            ${classData.code}
                        </span>
                        <span>•</span>
                        <span>${classData.teacher}</span>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                    <div class="flex items-center gap-4 text-xs text-slate-500">

                        <div class="flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                 viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7
                                         m10 0v-2c0-.656-.126-1.283-.356-1.857
                                         M7 20H2v-2a3 3 0 015.356-1.857
                                         M7 20v-2c0-.656.126-1.283.356-1.857
                                         m0 0a5.002 5.002 0 019.288 0
                                         M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span class="font-semibold text-slate-700">
                                ${classData.memberCount}
                            </span>
                        </div>

                        <div class="flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                 viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5
                                         a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414
                                         5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <span class="font-semibold text-slate-700">
                                ${classData.assignments}
                            </span>
                        </div>
                    </div>

                    <span class="px-3 py-1.5 bg-slate-100 text-slate-700 rounded-lg text-xs font-semibold">
                        ${classData.semester}
                    </span>
                </div>
            </div>
        </div>
        `;
}

document.addEventListener('DOMContentLoaded', function() {
    const classesListEl = document.getElementById('classes-list');
    const classesEmptyEl = document.getElementById('classes-empty');
    const classesCountEl = document.getElementById('classes-count');
    const filterButtons = document.querySelectorAll('.classes-filter');

    const rawClasses = (window.ucms && window.ucms.initialClasses) || [];
    const allClasses = rawClasses.map(normalizeClass);

    function renderClasses(filter) {
        let filtered = allClasses;

        if (filter === 'active') {
            filtered = allClasses.filter(c => !c.isArchived);
        } else if (filter === 'archived') {
            filtered = allClasses.filter(c => c.isArchived);
        }

        const total = filtered.length;

        if (total === 0) {
            classesListEl.setAttribute('hidden', 'true');
            classesEmptyEl.removeAttribute('hidden');
        } else {
            classesEmptyEl.setAttribute('hidden', 'true');
            classesListEl.removeAttribute('hidden');
            classesListEl.innerHTML = filtered.map(renderClassCard).join('');
        }

        classesCountEl.innerHTML =
            `<span class="font-semibold text-slate-700">${total}</span> ` +
            (total === 1 ? 'class in your list' : 'classes in your list');
    }

    // Filter button click handlers
    filterButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            const filter = btn.dataset.filter || 'all';

            filterButtons.forEach(b => {
                b.classList.remove(
                    'bg-white', 'text-slate-800', 'font-semibold',
                    'shadow-sm', 'border', 'border-slate-200'
                );
                b.classList.add('text-slate-600', 'font-medium');
            });

            if (filter === 'all') {
                btn.classList.add(
                    'bg-white', 'text-slate-800', 'font-semibold',
                    'shadow-sm', 'border', 'border-slate-200'
                );
            } else {
                btn.classList.add('bg-white', 'text-slate-800', 'font-semibold', 'shadow-sm');
            }

            renderClasses(filter);
        });
    });

    // Initial render
    renderClasses('all');

    // Join class form handler (placeholder)
    const joinForm = document.getElementById('join-class-form');
    if (joinForm) {
        joinForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const codeInput = this.querySelector('input[name="code"]');
            const code = codeInput ? codeInput.value.trim() : '';
            if (!code) return;
            alert(`Joining class with code: ${code}`);
            // পরে এখানে বাস্তব API call বসানো হবে
        });
    }
});
</script>
@endpush -->