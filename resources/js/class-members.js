// resources/js/class-members.js
console.log('UCMS >>> class-members.js LOADED');

let _apiRequest = null;
let _classId = null;
let _currentRole = null;

let membersInitialized = false;
let membersCache = {
    teacher: null,
    students: [],
};

export function initMembersModule({ apiRequest, classId, currentRole }) {
    _apiRequest = apiRequest;
    _classId = classId;
    _currentRole = currentRole;

    console.log('UCMS >>> initMembersModule()', { classId, currentRole });

    if (!membersInitialized) {
        membersInitialized = true;
        setupMembersEvents();
    }

    fetchMembers();
}

function setupMembersEvents() {
    const studentsList = document.getElementById('students-list');
    if (studentsList && !studentsList.dataset.bound) {
        studentsList.dataset.bound = '1';
        studentsList.addEventListener('click', async (e) => {
            const btn = e.target.closest('[data-remove-member-id]');
            if (!btn) return;

            const userId = btn.getAttribute('data-remove-member-id');
            if (!userId) return;

            if (!confirm('Remove this student from the class?')) return;

            await removeMember(userId, btn);
        });
    }
}

async function fetchMembers() {
    const teacherBox = document.getElementById('class-teacher-card');
    const listEl = document.getElementById('students-list');
    const countEl = document.getElementById('members-count');

    if (teacherBox) {
        teacherBox.innerHTML =
            '<p class="text-xs text-slate-500">Loading teacher info...</p>';
    }
    if (listEl) {
        listEl.innerHTML =
            '<p class="text-xs text-slate-500">Loading students...</p>';
    }
    if (countEl) {
        countEl.textContent = 'Loading members...';
    }

    try {
        // এখানে আমরা _apiRequest ব্যবহার না করে সরাসরি
        // তোমার Postman-এর মতোই /api/class/{id} হিট করছি
        const res = await fetch(`/api/class/${_classId}`, {
            method: 'GET',
            credentials: 'include', // cookie auth থাকলে
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        if (!res.ok) {
            throw new Error(`HTTP ${res.status}`);
        }

        const json = await res.json();

        // Postman অনুযায়ী shape: { data: { ... } }
        let classData = json;
        if (json && typeof json === 'object' && 'data' in json && json.data) {
            classData = json.data;
        }

        console.log('UCMS >>> fetchMembers classData =', classData);

        const teacher = classData.teacher || null;
        const students = Array.isArray(classData.students)
            ? classData.students
            : [];

        membersCache.teacher = teacher;
        membersCache.students = students;

        renderMembers();
    } catch (err) {
        console.error('UCMS >>> fetchMembers error', err);

        if (teacherBox) {
            teacherBox.innerHTML =
                '<p class="text-xs text-rose-500">Failed to load teacher info.</p>';
        }
        if (listEl) {
            listEl.innerHTML =
                '<p class="text-xs text-rose-500">Failed to load students.</p>';
        }
        if (countEl) {
            countEl.textContent = 'Failed to load members';
        }
    }
}

function renderMembers() {
    const teacherBox = document.getElementById('class-teacher-card');
    const listEl = document.getElementById('students-list');
    const countEl = document.getElementById('members-count');

    const teacher = membersCache.teacher;
    const students = Array.isArray(membersCache.students)
        ? membersCache.students
        : [];

    const canManage = _currentRole === 'Teacher' || _currentRole === 'Admin';

    console.log('UCMS >>> renderMembers()', {
        teacher,
        studentsCount: students.length,
        role: _currentRole,
    });

    // ---------- Teacher card ----------
    if (teacherBox) {
        if (!teacher) {
            teacherBox.innerHTML =
                '<p class="text-xs text-slate-500">No teacher assigned for this class.</p>';
        } else {
            const name =
                teacher.name ||
                teacher.full_name ||
                teacher.email ||
                'Unknown teacher';
            const email = teacher.email || '';
            const initials = getInitials(name || email);

            teacherBox.innerHTML = `
                <div class="flex items-center gap-3">
                    <div class="flex-shrink-0 w-10 h-10 rounded-full bg-indigo-600 text-white flex items-center justify-center text-xs font-semibold">
                        ${escapeHtml(initials)}
                    </div>
                    <div class="min-w-0">
                        <div class="flex items-center gap-1 text-sm font-semibold text-slate-800 truncate">
                            <span>${escapeHtml(name)}</span>
                            <span class="text-[13px]" aria-hidden="true">🎓</span>
                        </div>
                        ${email
                    ? `<div class="text-[11px] text-slate-500 truncate">${escapeHtml(
                        email
                    )}</div>`
                    : ''
                }
                        <div class="mt-1 inline-flex items-center rounded-full bg-indigo-50 text-[10px] font-medium text-indigo-700 px-2 py-0.5 border border-indigo-100">
                            Teacher
                        </div>
                    </div>
                </div>
            `;
        }
    }

    // ---------- Count ----------
    if (countEl) {
        countEl.textContent = `${students.length} students enrolled`;
    }

    // ---------- Students list ----------
    if (!listEl) return;

    if (students.length === 0) {
        listEl.innerHTML =
            '<p class="text-xs text-slate-500">No students enrolled yet.</p>';
        return;
    }

    const rowsHtml = students
        .map((s) => {
            const id = s.id ?? s.user_id ?? s.student_id;
            const name =
                s.name ||
                s.full_name ||
                s.email ||
                `Student #${id ?? ''}`;
            const email = s.email || '';
            const initials = getInitials(name || email);

            return `
                <div class="flex items-center justify-between gap-3 px-2 py-2 rounded-xl hover:bg-slate-50 border border-transparent hover:border-slate-200 transition">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-8 h-8 rounded-full bg-slate-100 text-slate-700 flex items-center justify-center text-[10px] font-semibold">
                            ${escapeHtml(initials)}
                        </div>
                        <div class="min-w-0">
                            <div class="text-xs font-semibold text-slate-800 truncate">
                                ${escapeHtml(name)}
                            </div>
                            <div class="text-[11px] text-slate-500 truncate">
                                ${email ? escapeHtml(email) : ''}
                            </div>
                        </div>
                    </div>
                    ${canManage
                    ? `<button
                                  type="button"
                                  class="inline-flex items-center px-2 py-1 rounded-full border border-rose-200 text-rose-600 text-[10px] font-medium hover:bg-rose-50"
                                  data-remove-member-id="${escapeHtml(id)}"
                               >
                                  Remove
                               </button>`
                    : ''
                }
                </div>
            `;
        })
        .join('');

    listEl.innerHTML = rowsHtml;
}

async function removeMember(userId, btn) {
    const originalText = btn ? btn.textContent : '';

    if (btn) {
        btn.disabled = true;
        btn.textContent = 'Removing...';
    }

    try {
        await _apiRequest(`/v1/classes/${_classId}/members/${userId}`, {
            method: 'DELETE',
        });

        membersCache.students = membersCache.students.filter((s) => {
            const id = s.id ?? s.user_id ?? s.student_id;
            return String(id) !== String(userId);
        });

        renderMembers();
    } catch (err) {
        console.error('UCMS >>> removeMember error', err);
        alert('Failed to remove member.');
    } finally {
        if (btn) {
            btn.disabled = false;
            btn.textContent = originalText || 'Remove';
        }
    }
}

// ---------- Helpers ----------
function getInitials(nameOrEmail) {
    if (!nameOrEmail) return '?';
    const base = String(nameOrEmail).trim();
    if (!base) return '?';

    const namePart = base.includes('@') ? base.split('@')[0] : base;
    const parts = namePart.split(/\s+/).filter(Boolean);

    if (parts.length === 1) {
        return parts[0].slice(0, 2).toUpperCase();
    }

    return (parts[0][0] + parts[1][0]).toUpperCase();
}

function escapeHtml(str) {
    if (str == null) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');
}
