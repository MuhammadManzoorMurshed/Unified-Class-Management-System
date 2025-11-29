// resources/js/class-show.js
import { initExamsMarksTab } from './class-exams-marks';
import { initChatsModule } from './class-chats';
import { initMembersModule } from './class-members';

const API_BASE = '/api';

function getToken() {
    return localStorage.getItem('ucms_token');
}

async function apiRequest(path, options = {}) {
    const token = getToken();

    const headers = Object.assign(
        { 'Accept': 'application/json' },
        options.headers || {}
    );

    if (options.body && !(options.body instanceof FormData)) {
        headers['Content-Type'] = 'application/json';
    }

    if (token) {
        headers['Authorization'] = `Bearer ${token}`;
    }

    const res = await fetch(`${API_BASE}${path}`, {
        ...options,
        headers,
    });

    let data = {};
    try {
        data = await res.json();
    } catch (e) {
        data = {};
    }

    if (!res.ok) {
        throw { status: res.status, data };
    }

    return data;
}

function setText(el, value) {
    if (!el) return;
    el.textContent = value ?? '–';
}

function formatDateShort(dateStr) {
    if (!dateStr) return '';
    const d = new Date(dateStr);
    if (Number.isNaN(d.getTime())) return dateStr;
    const dd = String(d.getDate()).padStart(2, '0');
    const mm = String(d.getMonth() + 1).padStart(2, '0');
    const yy = String(d.getFullYear()).slice(-2);
    return `${dd}/${mm}/${yy}`;
}

function formatDateTimeFriendly(dateStr) {
    if (!dateStr) return '-';

    // "YYYY-MM-DD HH:MM:SS" হলে একটু normalize করে নেই
    const normalized = dateStr.includes('T')
        ? dateStr
        : dateStr.replace(' ', 'T');

    const d = new Date(normalized);
    if (Number.isNaN(d.getTime())) return dateStr;

    const dd = String(d.getDate()).padStart(2, '0');
    const mm = String(d.getMonth() + 1).padStart(2, '0');
    const yy = String(d.getFullYear()).slice(-2);

    let hours = d.getHours();
    const minutes = String(d.getMinutes()).padStart(2, '0');
    const ampm = hours >= 12 ? 'PM' : 'AM';
    hours = hours % 12;
    if (hours === 0) hours = 12;
    const hh = String(hours).padStart(2, '0');

    return `${dd}/${mm}/${yy} ${hh}:${minutes} ${ampm}`;
}

export default async function initClassShowPage() {
    const classPageEl = document.getElementById('class-page');
    if (!classPageEl) return;

    const classId = Number(classPageEl.dataset.classId || 0);
    if (!classId) {
        console.error('Class ID missing on #class-page');
        return;
    }

    // ------------------ STATE ------------------
    let currentUser = null;
    let currentRole = null;

    let attendanceRecords = null;   // Teacher/Admin full records
    let myAttendanceRecords = null; // Student own records
    let studentsList = [];          // class members list (for Mark Attendance)
    let assignmentsCache = null;
    let assignmentSubmissions = {};

    // ------------------ DOM Refs ------------------
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

    const statsAssignmentsEl = document.getElementById('stats-assignments');
    const statsPostsEl = document.getElementById('stats-posts');
    const statsFilesEl = document.getElementById('stats-files');
    const statsEventsEl = document.getElementById('stats-events');

    // ------------------ Load current user ------------------
    try {
        const meRes = await apiRequest('/v1/me');
        currentUser = meRes.user || meRes;
        currentRole = currentUser.role?.role_name || currentUser.role || null;
    } catch (e) {
        console.error('Failed loading /v1/me', e);
    }

    // ------------------ 1) Load Class ------------------
    let classData = null;
    try {
        const res = await apiRequest(`/class/${classId}`);
        classData = res.data || res;

        setText(nameEl, classData.name || classData.title || 'Untitled class');
        setText(codeEl, classData.code || '—');
        setText(subjectEl, classData.subject || '—');
        setText(semesterEl, classData.semester || '—');
        setText(yearEl, classData.year || '—');
        setText(descriptionEl, classData.description || 'No description yet.');
        setText(breadcrumbCurrent, classData.name || 'Class');

        if (teacherEl) {
            const teacher = classData.teacher || {};
            setText(teacherEl, teacher.name || teacher.email || '—');
        }

        if (memberCountEl && typeof classData.member_count !== 'undefined') {
            memberCountEl.textContent = classData.member_count;
        }

        if (avatarEl && classData.code) {
            avatarEl.textContent = String(classData.code).split('-')[0].slice(0, 3).toUpperCase();
        }

        // সম্ভাব্য students list (ভবিষ্যতে backend থেকে adjust করা যাবে)
        if (Array.isArray(classData.students)) {
            studentsList = classData.students;
        } else if (Array.isArray(classData.members)) {
            studentsList = classData.members;
        } else if (Array.isArray(classData.enrollments)) {
            studentsList = classData.enrollments.map(e => e.student || e.user || e);
        }
    } catch (e) {
        console.error('Failed to load class details', e);
        setText(nameEl, 'Failed to load class');
        return;
    }

    // ------------------ 2) Overview Stats ------------------
    try {
        const assignmentsRes = await apiRequest(`/v1/classes/${classId}/assignments`);
        assignmentsCache = assignmentsRes.data || assignmentsRes;

        if (statsAssignmentsEl) {
            statsAssignmentsEl.textContent = Array.isArray(assignmentsCache)
                ? assignmentsCache.length
                : 0;
        }
    } catch (e) {
        console.error('Failed to load assignments', e);
        assignmentsCache = [];
        if (statsAssignmentsEl) statsAssignmentsEl.textContent = '0';
    }    

    if (statsPostsEl) statsPostsEl.textContent = '0';
    if (statsFilesEl) statsFilesEl.textContent = '0';
    if (statsEventsEl) statsEventsEl.textContent = '0';

    // ------------------ Attendance loaders ------------------
    async function ensureStudentAttendanceLoaded() {
        if (myAttendanceRecords) return;
        try {
            const res = await apiRequest(`/v1/classes/${classId}/my-attendance`);
            myAttendanceRecords = res.data || res;
        } catch (e) {
            console.error('Failed to load my-attendance', e);
            myAttendanceRecords = [];
        }
    }

    async function ensureTeacherAttendanceLoaded() {
        if (attendanceRecords) return;
        try {
            const res = await apiRequest(`/v1/classes/${classId}/attendance`);
            attendanceRecords = res.data || res;
        } catch (e) {
            console.error('Failed to load attendance', e);
            attendanceRecords = [];
        }
    }

    // ------------------ Student attendance view ------------------
    function renderStudentAttendanceView() {
        const container = document.getElementById('attendance-content');
        if (!container) return;

        const records = Array.isArray(myAttendanceRecords) ? myAttendanceRecords : [];

        if (!records.length) {
            container.innerHTML = `
                <p class="text-sm text-slate-500">No attendance records yet.</p>
            `;
            return;
        }

        // Unique dates
        const dateSet = new Set();
        records.forEach(r => {
            if (r.date) dateSet.add(r.date);
        });
        const dates = Array.from(dateSet).sort();
        const totalClasses = dates.length;

        // Count present
        const presentCount = records.filter(r => r.status === 'present').length;
        const absentCount = totalClasses - presentCount;
        const pct = totalClasses ? Math.round((presentCount * 100) / totalClasses) : 0;

        const studentId =
            (records[0].student && (records[0].student.student_id || records[0].student.id)) ||
            records[0].student_id ||
            currentUser?.id ||
            'N/A';

        const cellsRow = dates
            .map(date => {
                const rec = records.find(r => r.date === date);
                const status = rec ? rec.status : 'absent';
                return `<td class="px-3 py-2 text-xs text-center ${status === 'present' ? 'text-emerald-600' : 'text-red-500'
                    }">${status === 'present' ? '1' : '-'}</td>`;
            })
            .join('');

        const headerCells = dates
            .map(
                date => `
                <th class="px-3 py-2 text-[10px] text-slate-600 uppercase text-center">
                    ${formatDateShort(date)}
                </th>`
            )
            .join('');

        container.innerHTML = `
            <div class="space-y-4">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    <div class="bg-slate-50 rounded-xl p-3 text-center">
                        <div class="text-xs text-slate-500">Total Classes Held</div>
                        <div class="text-lg font-semibold text-slate-800">${totalClasses}</div>
                    </div>
                    <div class="bg-slate-50 rounded-xl p-3 text-center">
                        <div class="text-xs text-slate-500">Your Total Presence</div>
                        <div class="text-lg font-semibold text-emerald-600">${presentCount}</div>
                    </div>
                    <div class="bg-slate-50 rounded-xl p-3 text-center">
                        <div class="text-xs text-slate-500">Your Total Absence</div>
                        <div class="text-lg font-semibold text-red-500">${absentCount}</div>
                    </div>
                    <div class="bg-slate-50 rounded-xl p-3 text-center">
                        <div class="text-xs text-slate-500">Presence %</div>
                        <div class="text-lg font-semibold ${pct >= 75 ? 'text-emerald-600' : 'text-amber-500'
            }">${pct}%</div>
                    </div>
                </div>

                <div class="overflow-x-auto rounded-xl border border-slate-200">
                    <table class="min-w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-500 uppercase tracking-wide text-[10px]">
                            <tr>
                                <th class="px-3 py-2">ID</th>
                                ${headerCells}
                            </tr>
                        </thead>
                        <tbody class="bg-white">
                            <tr class="border-t border-slate-100">
                                <td class="px-3 py-2 text-xs font-medium text-slate-800">${studentId}</td>
                                ${cellsRow}
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        `;
    }

    // ------------------ Teacher Mark Attendance view ------------------
    function renderTeacherMarkAttendanceView(rootEl) {
        if (!rootEl) return;

        if (!Array.isArray(studentsList) || !studentsList.length) {
            rootEl.innerHTML = `
            <p class="text-sm text-slate-500">No students found for this class.</p>
        `;
            return;
        }

        // helper: student এর আইডি বের করার ফাংশন
        const getStudentId = (stu) =>
            stu.student_id ?? stu.id ?? stu.user_id ?? null;

        // ✅ 1) আইডি অনুযায়ী ascending sort
        const sortedStudents = [...studentsList]
            .filter((stu) => getStudentId(stu) !== null)
            .sort((a, b) => {
                const ida = getStudentId(a);
                const idb = getStudentId(b);
                const na = Number(ida);
                const nb = Number(idb);

                if (!Number.isNaN(na) && !Number.isNaN(nb)) {
                    return na - nb; // numeric sort
                }

                return String(ida).localeCompare(String(idb)); // fallback string sort
            });

        const today = new Date();
        const isoDate = today.toISOString().slice(0, 10); // YYYY-MM-DD
        const dayName = today.toLocaleDateString(undefined, { weekday: 'long' });

        const presentMap = {}; // id => true/false

        const rowsHtml = sortedStudents
            .map((stu) => {
                const id = getStudentId(stu);
                const displayId = stu.student_id ?? stu.id ?? id;
                if (!id) return '';
                return `
                <tr class="border-t border-slate-100" data-student-id="${id}">
                    <td class="px-3 py-2 text-xs font-medium text-slate-800">${displayId}</td>
                    <td class="px-3 py-2 text-xs text-center">
                        <button
                            type="button"
                            class="mark-present-btn inline-flex items-center justify-center px-3 py-1.5 text-xs rounded-lg border border-slate-300 bg-slate-50 text-slate-700 hover:bg-emerald-50 hover:border-emerald-500 transition"
                        >
                            Present
                        </button>
                    </td>
                </tr>
            `;
            })
            .join('');

        rootEl.innerHTML = `
        <div class="space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <div class="text-xs text-emerald-600 mb-[2px] uppercase tracking-wide">Date</div>
                    <div class="text-sm font-semibold text-slate-800">${isoDate}</div>
                </div>
                <div>
                    <div class="text-xs text-emerald-600 mb-[2px] text-right uppercase tracking-wide">Day</div>
                    <div class="text-sm font-semibold text-slate-800">${dayName}</div>
                </div>
            </div>

            <div class="overflow-x-auto rounded-xl border border-slate-200">
                <table class="min-w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-500 uppercase tracking-wide text-[10px]">
                        <tr>
                            <th class="px-3 py-2">ID</th>
                            <th class="px-3 py-2 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white">
                        ${rowsHtml}
                    </tbody>
                </table>
            </div>

            <div class="flex justify-end">
                <button
                    type="button"
                    id="submit-attendance-btn"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white rounded-xl text-xs font-semibold hover:bg-emerald-700 transition shadow-sm"
                >
                    <span>Save Attendance</span>
                </button>
            </div>
        </div>
    `;

        // toggle buttons
        rootEl.querySelectorAll('.mark-present-btn').forEach((btn) => {
            btn.addEventListener('click', () => {
                const row = btn.closest('tr');
                const id = row?.dataset.studentId;
                if (!id) return;

                if (presentMap[id]) {
                    // toggle off
                    presentMap[id] = false;
                    btn.classList.remove('bg-emerald-600', 'text-white', 'border-emerald-600');
                    btn.classList.add('bg-slate-50', 'text-slate-700', 'border-slate-300');
                } else {
                    presentMap[id] = true;
                    btn.classList.remove('bg-slate-50', 'text-slate-700', 'border-slate-300');
                    btn.classList.add('bg-emerald-600', 'text-white', 'border-emerald-600');
                }
            });
        });

        // submit handler
        const submitBtn = document.getElementById('submit-attendance-btn');
        if (submitBtn) {
            submitBtn.addEventListener('click', async () => {
                const studentsPayload = sortedStudents
                    .map((stu) => {
                        const sid = getStudentId(stu);
                        if (!sid) return null;
                        return {
                            student_id: sid,
                            status: presentMap[sid] ? 'present' : 'absent',
                        };
                    })
                    .filter(Boolean);

                const payload = {
                    date: isoDate,
                    students: studentsPayload,
                };

                try {
                    await apiRequest(`/v1/classes/${classId}/attendance`, {
                        method: 'POST',
                        body: JSON.stringify(payload),
                    });

                    // cache invalidate, যাতে sheet নতুন ডাটা নেয়
                    attendanceRecords = null;

                    // ✅ নতুন data লোড করো
                    if (typeof ensureTeacherAttendanceLoaded === 'function') {
                        await ensureTeacherAttendanceLoaded();
                    }

                    // ✅ 2) Attendance Sheet সাব-ট্যাবে চলে যাও
                    const sheetBtn = document.querySelector('.att-subtab-btn[data-subtab="sheet"]');
                    if (sheetBtn) {
                        sheetBtn.click();
                    } else {
                        alert('Attendance saved successfully.');
                    }
                } catch (e) {
                    console.error('Failed to save attendance', e);
                    alert('Failed to save attendance.');
                }
            });
        }
    }

    // ------------------ Teacher Attendance Sheet view ------------------
    function renderTeacherAttendanceSheetView(rootEl) {
        if (!rootEl) return;

        const records = Array.isArray(attendanceRecords) ? attendanceRecords : [];
        if (!records.length) {
            rootEl.innerHTML = `
                <p class="text-sm text-slate-500">No attendance records for this class yet.</p>
            `;
            return;
        }

        const dateSet = new Set();
        const studentsMap = new Map();

        records.forEach(r => {
            if (!r.date) return;
            dateSet.add(r.date);

            const stu = r.student || r.user || {};
            const id = stu.id || r.student_id || r.user_id;
            if (!id) return;

            if (!studentsMap.has(id)) {
                studentsMap.set(id, {
                    id,
                    displayId: stu.student_id || stu.id || r.student_id || r.user_id || id,
                    attendanceByDate: {}, // date => status
                    present: 0,
                    absent: 0,
                });
            }

            const entry = studentsMap.get(id);
            entry.attendanceByDate[r.date] = r.status;

            if (r.status === 'present') entry.present += 1;
            else if (r.status === 'absent') entry.absent += 1;
        });

        const dates = Array.from(dateSet).sort();
        const totalClasses = dates.length;

        const headerCells = dates
            .map(
                date => `
                <th class="px-3 py-2 text-[10px] text-slate-600 uppercase text-center">
                    ${formatDateShort(date)}
                </th>`
            )
            .join('');

        const rowsHtml = Array.from(studentsMap.values())
            .map(stu => {
                const cells = dates
                    .map(date => {
                        const status = stu.attendanceByDate[date] || 'absent';
                        return `<td class="px-3 py-2 text-xs text-center ${status === 'present' ? 'text-emerald-600' : 'text-red-500'
                            }">${status === 'present' ? '1' : '-'}</td>`;
                    })
                    .join('');

                const present = stu.present;
                const absent = totalClasses - present;
                const pct = totalClasses ? Math.round((present * 100) / totalClasses) : 0;

                return `
                    <tr class="border-t border-slate-100">
                        <td class="px-3 py-2 text-xs font-medium text-slate-800">${stu.displayId}</td>
                        ${cells}
                        <td class="px-3 py-2 text-xs text-center text-emerald-600">${present}</td>
                        <td class="px-3 py-2 text-xs text-center text-red-500">${absent}</td>
                        <td class="px-3 py-2 text-xs text-center ${pct >= 75 ? 'text-emerald-600' : 'text-amber-500'
                    }">${pct}%</td>
                    </tr>
                `;
            })
            .join('');

        rootEl.innerHTML = `
            <div class="space-y-4">
                <div class="text-sm text-slate-700">
                    Total classes held: <span class="font-semibold">${totalClasses}</span>
                </div>

                <div class="overflow-x-auto rounded-xl border border-slate-200">
                    <table class="min-w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-500 uppercase tracking-wide text-[10px]">
                            <tr>
                                <th class="px-3 py-2">ID</th>
                                ${headerCells}
                                <th class="px-3 py-2 text-center">Total Present</th>
                                <th class="px-3 py-2 text-center">Total Absent</th>
                                <th class="px-3 py-2 text-center">Present %</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white">
                            ${rowsHtml}
                        </tbody>
                    </table>
                </div>
            </div>
        `;
    }

    // ------------------ Teacher Attendance main (tabs inside attendance) ------------------
    function renderTeacherAttendanceView() {
        const container = document.getElementById('attendance-content');
        if (!container) return;

        container.innerHTML = `
            <div class="space-y-4">
                <div class="flex justify-center items-center gap-2 rounded-xl bg-slate-100 p-1 text-xs w-1/2 mx-auto">
                    <button
                        type="button"
                        class="att-subtab-btn px-3 py-1.5 rounded-lg font-medium bg-white text-slate-800 shadow-sm"
                        data-subtab="mark"
                    >
                        Mark Attendance
                    </button>
                    <button
                        type="button"
                        class="att-subtab-btn px-3 py-1.5 rounded-lg font-medium text-slate-600"
                        data-subtab="sheet"
                    >
                        Attendance Sheet
                    </button>
                </div>

                <div id="att-subtab-mark" class="space-y-3"></div>
                <div id="att-subtab-sheet" class="hidden space-y-3"></div>
            </div>
        `;

        const markRoot = document.getElementById('att-subtab-mark');
        const sheetRoot = document.getElementById('att-subtab-sheet');

        const buttons = container.querySelectorAll('.att-subtab-btn');

        function setSubtab(tab) {
            buttons.forEach(btn => {
                btn.classList.remove('bg-white', 'text-slate-800', 'shadow-sm');
                btn.classList.add('text-slate-600');
            });
            const active = container.querySelector(`.att-subtab-btn[data-subtab="${tab}"]`);
            if (active) {
                active.classList.add('bg-white', 'text-slate-800', 'shadow-sm');
                active.classList.remove('text-slate-600');
            }

            if (tab === 'mark') {
                if (markRoot) {
                    markRoot.classList.remove('hidden');
                    renderTeacherMarkAttendanceView(markRoot);
                }
                if (sheetRoot) sheetRoot.classList.add('hidden');
            } else {
                if (markRoot) markRoot.classList.add('hidden');
                if (sheetRoot) {
                    sheetRoot.classList.remove('hidden');
                    renderTeacherAttendanceSheetView(sheetRoot);
                }
            }
        }

        buttons.forEach(btn => {
            btn.addEventListener('click', () => {
                const tab = btn.dataset.subtab || 'mark';
                setSubtab(tab);
            });
        });

        // default subtab
        setSubtab('mark');
    }

    async function loadMySubmissionsForAssignments() {
        if (currentRole !== 'Student') return;
        if (!Array.isArray(assignmentsCache) || !assignmentsCache.length) return;

        const map = { ...assignmentSubmissions };

        for (const a of assignmentsCache) {
            if (!a.id || map[a.id] !== undefined) continue;

            try {
                const res = await apiRequest(`/v1/assignments/${a.id}/my-submission`);
                map[a.id] = res.data || res;
            } catch (e) {
                // 404 মানে এখনো submit করেনি
                if (e.status === 404) {
                    map[a.id] = null;
                } else {
                    console.error('Failed to load my-submission for assignment', a.id, e);
                }
            }
        }

        assignmentSubmissions = map;
        renderAssignmentsTab(); // submission ডাটা আসার পর আবার কার্ড রেন্ডার
    }    

    // ------------------ Tabs ------------------
    const tabButtons = document.querySelectorAll('.tab-btn');
    const tabContents = {
        overview: document.getElementById('tab-content-overview'),
        posts: document.getElementById('tab-content-posts'),
        assignments: document.getElementById('tab-content-assignments'),
        exams: document.getElementById('tab-content-exams'),
        files: document.getElementById('tab-content-files'),
        members: document.getElementById('tab-content-members'),
        attendance: document.getElementById('tab-content-attendance') || null,
    };

    // ------------------ Assignment Modal Setup ------------------
    const assignmentModal = document.getElementById('assignment-modal');
    const assignmentForm = document.getElementById('assignment-form');
    const btnModalClose = document.getElementById('assignment-modal-close');
    const btnModalCancel = document.getElementById('assignment-modal-cancel');
    const btnTabCreate = document.getElementById('btn-open-assignment-modal');
    const btnQuickCreate = document.getElementById('btn-quick-create-assignment');

    function openAssignmentModal() {
        if (!assignmentModal) return;

        // reset form
        if (assignmentForm) {
            assignmentForm.reset();

            // deadline default: আজকে রাত ২৩:৫৯
            const dl = document.getElementById('assignment_deadline');
            if (dl) {
                const now = new Date();
                now.setHours(23, 59, 0, 0);
                const local = new Date(now.getTime() - now.getTimezoneOffset() * 60000)
                    .toISOString()
                    .slice(0, 16);
                dl.value = local;
            }

            const maxMarksInput = document.getElementById('assignment_max_marks');
            if (maxMarksInput && !maxMarksInput.value) {
                maxMarksInput.value = '100';
            }
        }

        assignmentModal.classList.remove('hidden');
        assignmentModal.classList.add('flex');
    }

    function closeAssignmentModal() {
        if (!assignmentModal) return;
        assignmentModal.classList.add('hidden');
        assignmentModal.classList.remove('flex');
    }

    if (btnModalClose) btnModalClose.addEventListener('click', closeAssignmentModal);
    if (btnModalCancel) btnModalCancel.addEventListener('click', closeAssignmentModal);

    // শুধু Teacher/Admin এর জন্য create বাটনগুলো enable করা
    const isTeacherLike = currentRole === 'Teacher' || currentRole === 'Admin';
    if (btnTabCreate) {
        if (isTeacherLike) {
            btnTabCreate.classList.remove('hidden');
            btnTabCreate.addEventListener('click', openAssignmentModal);
        } else {
            btnTabCreate.classList.add('hidden');
        }
    }
    if (btnQuickCreate) {
        if (isTeacherLike) {
            btnQuickCreate.addEventListener('click', openAssignmentModal);
        } else {
            // স্টুডেন্ট হলে quick actions-এ এই অপশনও ideally hide করা উচিত
            // চাইলে classList.add('hidden') করতে পারো
        }
    }

    // form submit -> API call (file সহ)
    if (assignmentForm && isTeacherLike) {
        assignmentForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const titleEl = document.getElementById('assignment_title');
            const descEl = document.getElementById('assignment_description');
            const instrEl = document.getElementById('assignment_instructions');
            const deadlineEl = document.getElementById('assignment_deadline');
            const maxMarksEl = document.getElementById('assignment_max_marks');
            const typeEl = document.getElementById('assignment_type');
            const fileEl = document.getElementById('assignment_file');

            const title = titleEl?.value?.trim();
            const description = descEl?.value?.trim();
            const instructions = instrEl?.value?.trim() || '';
            let deadlineVal = deadlineEl?.value || '';
            const maxMarksVal = maxMarksEl?.value || '';
            const typeVal = typeEl?.value || 'Assignment';
            const file = fileEl?.files?.[0] || null;

            if (!title || !description || !deadlineVal) {
                alert('Title, description এবং deadline ফিল্ডগুলো আবশ্যক।');
                return;
            }

            // datetime-local => "YYYY-MM-DD HH:MM:SS"
            if (deadlineVal.includes('T')) {
                deadlineVal = deadlineVal.replace('T', ' ') + ':00';
            }

            const formData = new FormData();
            formData.append('title', title);
            formData.append('description', description);
            formData.append('instructions', instructions);
            formData.append('deadline', deadlineVal);
            if (maxMarksVal !== '') formData.append('max_marks', maxMarksVal);
            formData.append('assignment_type', typeVal);
            formData.append('is_published', '1');

            if (file) {
                formData.append('file', file);
            }

            try {
                const res = await apiRequest(`/v1/classes/${classId}/assignments`, {
                    method: 'POST',
                    body: formData,
                });

                const created = res.data || res;

                if (!Array.isArray(assignmentsCache)) {
                    assignmentsCache = [];
                }
                assignmentsCache.push(created);

                closeAssignmentModal();
                renderAssignmentsTab();
                alert('Assignment created successfully.');
            } catch (err) {
                console.error('Create assignment failed', err);
                const msg = err?.data?.message || 'Failed to create assignment.';
                alert(msg);
            }
        });
    }


    function showAttendanceTab() {
        if (currentRole === 'Student') {
            ensureStudentAttendanceLoaded().then(() => {
                renderStudentAttendanceView();
            });
        } else {
            ensureTeacherAttendanceLoaded().then(() => {
                renderTeacherAttendanceView();
            });
        }
    }

    function renderAssignmentsTab() {
        const container = document.getElementById('assignments-list');
        if (!container) return;

        const subtitleEl = document.getElementById('assignments-subtitle');
        if (subtitleEl) {
            subtitleEl.textContent =
                (currentRole === 'Teacher' || currentRole === 'Admin')
                ? 'Create and manage assignments for this class ➜'
                    : 'View assignments and deadlines for this class.';
        }

        const items = Array.isArray(assignmentsCache) ? assignmentsCache : [];

        if (!items.length) {
            container.innerHTML = `
                <p class="text-sm text-slate-500">No assignments created yet.</p>
            `;
            return;
        }

        const now = new Date();

        const cards = items.map(a => {
            const d = a.deadline ? new Date(a.deadline.includes('T') ? a.deadline : a.deadline.replace(' ', 'T')) : null;

            let statusLabel = '';
            let statusClass = '';

            const isDraft = !a.is_published;
            const isFuture = d && d > now;
            const isPast = d && d <= now;

            if (currentRole === 'Teacher' || currentRole === 'Admin') {
                if (isDraft) {
                    statusLabel = 'Draft';
                    statusClass = 'bg-amber-50 text-amber-700 border border-amber-200';
                } else if (isFuture) {
                    statusLabel = 'Open';
                    statusClass = 'bg-emerald-50 text-emerald-700 border border-emerald-200';
                } else if (isPast) {
                    statusLabel = 'Past Due';
                    statusClass = 'bg-rose-50 text-rose-700 border border-rose-200';
                }
            } else {
                if (isFuture) {
                    statusLabel = 'Open';
                    statusClass = 'bg-emerald-50 text-emerald-700 border border-emerald-200';
                } else if (isPast) {
                    statusLabel = 'Past Due';
                    statusClass = 'bg-rose-50 text-rose-700 border border-rose-200';
                }
            }

            const fileLink = a.file_url
                ? `<a href="${a.file_url}" target="_blank"
                     class="inline-flex items-center gap-1 font-bold text-xs text-emerald-600 hover:text-emerald-700 mt-2">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5-5 5 5M12 5v11"/>
                        </svg>
                        <span>${a.file_name || 'Download File'}</span>
                   </a>`
                : '';

            const deadlineDisplay = formatDateTimeFriendly(a.deadline);

            // ---- Student submission status ----
            const mySub = assignmentSubmissions && a.id ? assignmentSubmissions[a.id] : undefined;
            const hasSubmitted = !!mySub;

            let studentActionHtml = '';
            if (currentRole === 'Student') {
                if (hasSubmitted) {
                    const myMarks = (mySub.marks ?? mySub.marks_obtained);
                    const marksHtml = (myMarks !== undefined && myMarks !== null)
                        ? `<span class="text-xs text-emerald-700 font-medium">
                               Marks: ${myMarks} / ${a.max_marks ?? 100}
                           </span>`
                        : '';

                    studentActionHtml = `
                        <div class="flex flex-wrap items-center gap-2 mt-3">
                            <span class="inline-flex items-center px-3 py-1.5 rounded-full bg-emerald-50 text-emerald-700 text-xs font-medium">
                                Submitted
                            </span>
                            ${marksHtml}
                        </div>
                    `;
                } else {
                    studentActionHtml = `
                        <button data-assignment-id="${a.id}"
                            class="btn-submit-assignment inline-flex items-center 
                                   px-3 py-2 bg-indigo-600 text-white rounded-lg text-xs
                                   hover:bg-indigo-700 transition">
                            Submit Assignment
                        </button>
                    `;
                }
            }

            const teacherBtn = (currentRole === 'Teacher' || currentRole === 'Admin')
                ? `<button data-review-id="${a.id}"
                       class="btn-review-submissions inline-flex items-center 
                              px-3 py-2 bg-emerald-600 font-bold text-white rounded-lg text-xs
                              hover:bg-emerald-700 transition">
                    View Submissions
               </button>`
                : '';

            return `
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm hover:shadow-md transition flex flex-col gap-3">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="text-base font-semibold text-slate-800 truncate">${a.title}</div>
                            <div class="text-xs text-slate-500 mt-1 line-clamp-2">
                                ${a.description || ''}
                            </div>
                        </div>
                        ${statusLabel ? `
                            <span class="px-2 py-1 rounded-lg text-[10px] font-semibold ${statusClass}">
                                ${statusLabel}
                            </span>
                        ` : ''}
                    </div>
    
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-2 text-xs text-slate-600">
                        <div><span class="font-medium">Type:</span> ${a.assignment_type}</div>
                        <div><span class="font-medium">Max Marks:</span> ${a.max_marks}</div>
                        <div class="md:col-span-2">
                            <span class="font-medium">Deadline:</span> ${deadlineDisplay}
                        </div>
                    </div>
    
                    ${fileLink ? `<div>${fileLink}</div>` : ''}
    
                    <div class="flex flex-wrap gap-2 justify-end">
                        ${studentActionHtml}
                        ${teacherBtn}
                    </div>
                </div>
            `;
        }).join('');

        container.innerHTML = cards;
    }
    
    

    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('btn-submit-assignment')) {
            const id = e.target.dataset.assignmentId;
            openStudentSubmitModal(id);
        }
    });
    
    function openStudentSubmitModal(id) {
        document.getElementById("submit_assignment_id").value = id;
        document.getElementById("submit-assignment-modal").classList.remove("hidden");
        document.getElementById("submit-assignment-modal").classList.add("flex");
    }

    function closeStudentSubmitModal() {
        document.getElementById("submit-assignment-modal").classList.add("hidden");
        document.getElementById("submit-assignment-modal").classList.remove("flex");
    }

    document.getElementById("submit-assignment-cancel").onclick = closeStudentSubmitModal;

    document.getElementById("submit-assignment-form").addEventListener("submit", async (e) => {
        e.preventDefault();

        const assignmentId = document.getElementById("submit_assignment_id").value;
        const file = document.getElementById("submit_assignment_file").files[0];

        const fd = new FormData();
        fd.append('file', file);

        try {
            await apiRequest(`/v1/assignments/${assignmentId}/submit`, {
                method: "POST",
                body: fd
            });

            closeStudentSubmitModal();
            alert("Assignment submitted successfully!");

        } catch (err) {
            console.error(err);
            alert("Failed to submit assignment.");
        }
    });
    
    document.addEventListener("click", function (e) {
        if (e.target.classList.contains('btn-review-submissions')) {
            const id = e.target.dataset.reviewId;
            loadReviewTable(id);
        }
    });

    document.addEventListener('click', async function (e) {
        if (!e.target.classList.contains('btn-save-marks')) return;

        const btn = e.target;
        const reviewed = btn.dataset.reviewed === '1';
        if (reviewed) return;

        const row = btn.closest('tr');
        const input = row ? row.querySelector('.marks-input') : null;
        if (!input) return;

        const submissionId = input.dataset.subId;
        const marks = input.value;

        if (marks === '' || marks === null) {
            alert('Please enter marks before saving.');
            return;
        }

        try {
            await apiRequest(`/v1/submissions/${submissionId}/marks`, {
                method: "POST",
                body: JSON.stringify({ marks })
            });

            // UI update → Reviewed
            input.disabled = true;
            btn.textContent = 'Reviewed';
            btn.dataset.reviewed = '1';
            btn.className =
                'btn-save-marks text-xs px-2 py-1 rounded border w-24 text-center ' +
                'bg-emerald-50 text-emerald-700 border-emerald-300 cursor-default';
        } catch (err) {
            console.error(err);
            alert('Failed to save marks.');
        }
    });
    
    
    
    async function loadReviewTable(assignmentId) {
        const res = await apiRequest(`/v1/assignments/${assignmentId}/submissions`);
        const list = res.data || res;

        const tbody = document.getElementById("review-submissions-body");
        if (!tbody) return;

        const rows = list.map(s => {
            const submittedAtDisplay = formatDateTimeFriendly(
                s.submitted_at || s.submission_date || s.created_at || ''
            );

            const marksVal = (s.marks ?? s.marks_obtained ?? '');
            const hasMarks = marksVal !== '' && marksVal !== null && marksVal !== undefined;
            const fileUrl = s.file_url || '#';
            const fileName = s.file_name || 'submission';

            const btnBaseClass =
                'btn-save-marks text-xs py-1 rounded border w-24 text-center';

            const btnClass = hasMarks
                ? `${btnBaseClass} bg-emerald-50 text-emerald-700 border-emerald-300 cursor-default`
                : `${btnBaseClass} bg-slate-800 text-white border-slate-800 hover:bg-black`;

            const btnLabel = hasMarks ? 'Reviewed' : 'Save';

            return `
                <tr class="border-t">
                    <td class="px-3 py-2 text-xs text-center">${s.student_id}</td>
    
                    <td class="px-3 py-2 text-xs text-center">
                        <a href="${fileUrl}" target="_blank"
                           class="text-indigo-600 text-xs inline-block w-20 text-center">
                            View
                        </a>
                        <span>|</span>
                        <a href="${fileUrl}" download="${fileName}"
                           class="text-indigo-600 text-xs inline-block w-20 text-center">
                            Download
                        </a>
                    </td>
    
                    <td class="px-3 py-2 text-xs text-center">
                        ${submittedAtDisplay}
                    </td>
    
                    <td class="px-3 py-2 text-xs text-center">
                        <div class="inline-flex items-center gap-2 justify-center">
                            <input
                                type="number"
                                value="${hasMarks ? marksVal : ''}"
                                data-sub-id="${s.id}"
                                class="marks-input border rounded py-1 text-xs w-24 text-center"
                                ${hasMarks ? 'disabled' : ''}
                            />
                            <button
                                type="button"
                                class="${btnClass}"
                                data-sub-id="${s.id}"
                                ${hasMarks ? 'data-reviewed="1"' : ''}
                            >
                                ${btnLabel}
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');

        tbody.innerHTML = rows;

        const modal = document.getElementById("review-submissions-modal");
        if (modal) {
            modal.classList.remove("hidden");
            modal.classList.add("flex");
        }
    }
    
    
      
    
    document.getElementById("review-submissions-close").onclick = () => {
        const modal = document.getElementById("review-submissions-modal");
        if (!modal) return;
        modal.classList.add("hidden");
        modal.classList.remove("flex");
    };

    
    // document.addEventListener("blur", async function (e) {
    //     if (e.target.classList.contains("marks-input")) {
    //         const id = e.target.dataset.subId;
    //         const marks = e.target.value;

    //         await apiRequest(`/v1/submissions/${id}/marks`, {
    //             method: "POST",
    //             body: JSON.stringify({ marks })
    //         });
    //     }
    // }, true);
    

    function setActiveTab(target) {
        tabButtons.forEach(btn => {
            btn.classList.remove('bg-indigo-600', 'text-white');
            btn.classList.add('text-slate-600', 'hover:text-slate-800', 'hover:bg-slate-50');
        });

        Object.values(tabContents).forEach(section => {
            if (section) section.classList.add('hidden');
        });

        const activeButton = Array.from(tabButtons).find(btn => btn.dataset.tab === target);
        if (activeButton) {
            activeButton.classList.remove('text-slate-600', 'hover:text-slate-800', 'hover:bg-slate-50');
            activeButton.classList.add('bg-indigo-600', 'text-white');
        }

        if (tabContents[target]) {
            tabContents[target].classList.remove('hidden');
        }

        if (target === 'attendance') {
            showAttendanceTab();
        }

        if (target === 'assignments') {
            const loadAndRender = () => {
                renderAssignmentsTab();
                if (currentRole === 'Student') {
                    loadMySubmissionsForAssignments();
                }
            };

            if (!assignmentsCache) {
                apiRequest(`/v1/classes/${classId}/assignments`)
                    .then(res => {
                        assignmentsCache = res.data || res;
                        loadAndRender();
                    })
                    .catch(err => {
                        console.error('Reload assignments failed', err);
                        assignmentsCache = [];
                        loadAndRender();
                    });
            } else {
                loadAndRender();
            }
        }

        if (target === 'exams') {
            initExamsMarksTab({
                classId,
                currentRole,
                apiRequest,
                formatDateShort,
                studentsList,     // 👈 ক্লাসের সব স্টুডেন্ট লিস্ট পাঠাচ্ছি
            });
        } 
        
        if (target === 'posts') {
            initChatsModule({
                apiRequest,
                classId,
                currentUser,
            });
        }
        
        if (target === 'members') {
            initMembersModule({
                apiRequest,
                classId,
                currentRole, // "Teacher" / "Student" / "Admin"
            });
        }
    }

    tabButtons.forEach(button => {
        button.addEventListener('click', function () {
            const target = this.dataset.tab || 'overview';
            setActiveTab(target);
        });
    });

    // ডিফল্ট ট্যাব (তুমি আগে posts করে দিয়েছ)
    setActiveTab('posts');
}
