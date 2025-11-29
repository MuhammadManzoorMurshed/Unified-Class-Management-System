// resources/js/class-exams-marks.js

// Exams & Marks UI logic (Teacher + Student)
// This module is imported from class-show.js

let _apiRequest = null;
let _formatDateShort = null;
let _classId = null;
let _currentRole = null;

let examsCache = null;
let currentExamId = null;
let currentExamMeta = null;

let _studentsList = [];
let examSelectorBound = false;

export function initExamsMarksTab({ classId, currentRole, apiRequest, formatDateShort, studentsList = [], }) {
    _apiRequest = apiRequest;
    _formatDateShort = formatDateShort;
    _classId = classId;
    _currentRole = currentRole;
    _studentsList = Array.isArray(studentsList) ? studentsList : [];

    const tabRoot = document.getElementById('tab-content-exams');
    if (!tabRoot) return;

    const teacherPanel = document.getElementById('exams-marks-teacher-panel');
    const studentPanel = document.getElementById('exams-marks-student-panel');
    const subtitleEl = document.getElementById('exams-marks-subtitle');

    if (subtitleEl) {
        if (currentRole === 'Student') {
            subtitleEl.textContent = 'See exam-wise marks you have obtained in this class.';
        } else if (currentRole === 'Teacher' || currentRole === 'Admin') {
            subtitleEl.textContent = 'Create and manage exams for this class ➜';
        } else {
            subtitleEl.textContent = 'View exams and marks for this class.';
        }
    }

    if (teacherPanel) {
        if (currentRole === 'Teacher' || currentRole === 'Admin') {
            teacherPanel.classList.remove('hidden');
        } else {
            teacherPanel.classList.add('hidden');
        }
    }

    if (studentPanel) {
        if (currentRole === 'Student') {
            studentPanel.classList.remove('hidden');
        } else {
            studentPanel.classList.add('hidden');
        }
    }


    // একবারই ইনিশিয়ালাইজ করব
    if (!tabRoot.dataset.emInit) {
        tabRoot.dataset.emInit = '1';

        if (currentRole === 'Teacher' || currentRole === 'Admin') {
            setupTeacherExamsMarks();
        }

        if (currentRole === 'Teacher' || currentRole === 'Admin') {
            setupExamModal();   // ← এখানেই নতুন modal init হবে
        }

        if (currentRole === 'Student') {
            loadStudentMarks();
        }
    } else {
        // ট্যাবে আবার ঢুকলে:
        if (currentRole === 'Teacher' || currentRole === 'Admin') {
            if (currentExamId) {
                loadExamMarksForTeacher(currentExamId);
            }
        }

        if (currentRole === 'Student') {
            loadStudentMarks();
        }
    }
}

function renderExamOptions() {
    const selector = document.getElementById('exam-selector');
    if (!selector) return;

    selector.innerHTML = '';

    if (!examsCache || !examsCache.length) {
        selector.innerHTML = '<option value="" disabled selected>No exams found</option>';
        return;
    }

    selector.innerHTML = '<option value="" disabled selected>Select an exam</option>';

    examsCache.forEach(ex => {
        const opt = document.createElement('option');
        opt.value = ex.id;

        const type = ex.exam_type || ex.title || 'Exam';
        const datePart = ex.exam_date ? _formatDateShort(ex.exam_date) : '';
        opt.textContent = datePart ? `${type} – ${datePart}` : type;

        selector.appendChild(opt);
    });
}


// ---------- Teacher side ----------

async function setupTeacherExamsMarks() {
    const selector = document.getElementById('exam-selector');
    if (!selector) return;

    selector.innerHTML = '<option value="" disabled selected>Loading exams...</option>';

    try {
        const res = await _apiRequest(`/v1/classes/${_classId}/exams`);
        examsCache = Array.isArray(res.data) ? res.data : (Array.isArray(res) ? res : []);
    } catch (e) {
        console.error('Failed to load exams', e);
        examsCache = [];
    }

    renderExamOptions();

    if (!examSelectorBound) {
        selector.addEventListener('change', (e) => {
            const examId = Number(e.target.value);
            if (!examId) return;

            currentExamId = examId;
            currentExamMeta = examsCache.find(ex => ex.id === examId) || null;
            updateExamHeader();
            loadExamMarksForTeacher(examId);
        });
        examSelectorBound = true;
    }
}


function updateExamHeader() {
    const titleEl = document.getElementById('marks-entry-exam-title');
    const metaEl = document.getElementById('marks-entry-exam-meta');

    if (!currentExamMeta) {
        if (titleEl) titleEl.textContent = 'Selected exam';
        if (metaEl) metaEl.textContent = '';
        return;
    }

    if (titleEl) {
        titleEl.textContent = currentExamMeta.title || 'Selected exam';
    }

    if (metaEl) {
        const parts = [];
        if (currentExamMeta.exam_type) parts.push(currentExamMeta.exam_type);
        if (currentExamMeta.exam_date) parts.push(_formatDateShort(currentExamMeta.exam_date));
        if (currentExamMeta.total_marks != null) parts.push(`Total: ${currentExamMeta.total_marks}`);
        metaEl.textContent = parts.join(' • ');
    }
}

function buildRowsForMarks(marksRaw) {
    const marksArray = Array.isArray(marksRaw) ? marksRaw : [];

    // প্রথমে exam এর marks গুলোকে map করে রাখি: student_id => markRow
    const marksByStudentId = new Map();
    marksArray.forEach(row => {
        const stu = row.student || {};
        const sid = row.student_id ?? stu.id;
        if (!sid) return;

        marksByStudentId.set(Number(sid), row);
    });

    const students = Array.isArray(_studentsList) ? _studentsList : [];

    // যদি studentsList পাওয়া যায় → প্রতিটি স্টুডেন্টের জন্য রো বানাবো
    if (students.length) {
        return students
            .map(stu => {
                const sid = Number(stu.id);
                if (!sid) return null;

                const markRow = marksByStudentId.get(sid);

                return {
                    student_id: sid,
                    student: {
                        name: stu.name || '',
                        email: stu.email || '',
                    },
                    marks_obtained: markRow
                        ? (markRow.marks_obtained ?? markRow.marks ?? null)
                        : null,
                };
            })
            .filter(Boolean);
    }

    // fallback: যদি studentsList না থাকে, আগের logic-এর মতো marks থেকে রো বানাই
    if (marksArray.length) {
        return marksArray.map(row => {
            const stu = row.student || {};
            const sid = row.student_id ?? stu.id;
            return {
                student_id: sid,
                student: {
                    name: stu.name || '',
                    email: stu.email || '',
                },
                marks_obtained: row.marks_obtained ?? row.marks ?? null,
            };
        });
    }

    return [];
}


async function loadExamMarksForTeacher(examId) {
    const card = document.getElementById('marks-entry-card');
    const wrapper = document.getElementById('marks-entry-table-wrapper');

    if (wrapper) {
        wrapper.innerHTML = '<p class="text-sm text-slate-500">Loading students and marks...</p>';
    }
    if (card) {
        card.classList.remove('hidden');
    }

    let marks = [];
    try {
        const res = await _apiRequest(`/v1/exams/${examId}/marks`);
        // MarksController@examMarks → { message, exam: {...}, marks: [...] }
        marks = Array.isArray(res.marks) ? res.marks : [];
        if (res.exam) {
            currentExamMeta = Object.assign({}, currentExamMeta || {}, res.exam);
            updateExamHeader();
        }
    } catch (e) {
        console.error('Failed to load exam marks', e);
        marks = [];
    }

    const rows = buildRowsForMarks(marks);
    renderMarksTable(rows);
}

function renderMarksTable(rows) {
    const wrapper = document.getElementById('marks-entry-table-wrapper');
    if (!wrapper) return;

    const data = Array.isArray(rows) ? rows : [];

    if (!data.length) {
        wrapper.innerHTML = '<p class="text-sm text-slate-500">No students found for this exam/class.</p>';
        return;
    }

    let html = `
        <div class="rounded-2xl border border-slate-200 overflow-hidden">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-3 py-2 text-left text-[11px] font-semibold tracking-wide text-slate-600 uppercase">
                            ID
                        </th>
                        <th class="px-3 py-2 text-left text-[11px] font-semibold tracking-wide text-slate-600 uppercase">
                            Marks
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
    `;

    data.forEach(row => {
        const studentId = row.student_id ?? (row.student && row.student.id) ?? '';
        if (!studentId) return;

        const isMarked = row.marks_obtained != null;
        const markValue = isMarked ? row.marks_obtained : '';
        const inputId = `exam-mark-${currentExamId}-${studentId}`;

        html += `
            <tr class="odd:bg-slate-50 hover:bg-slate-100/60 transition-colors">
                <td class="px-3 py-2 align-middle text-xs text-slate-700">
                    ${studentId}
                </td>
                <td class="px-3 py-2 align-middle">
                    <div class="flex items-center gap-2">
                        <input
                            id="${inputId}"
                            type="number"
                            step="0.01"
                            class="w-24 rounded-lg border border-slate-300 bg-white px-2 py-1 text-xs text-slate-800 shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 disabled:bg-slate-100 disabled:text-slate-500"
                            value="${markValue !== '' ? markValue : ''}"
                            ${isMarked ? 'disabled' : ''}
                        />
                        ${isMarked
                ? `<button
                                       type="button"
                                       class="inline-flex items-center rounded-lg bg-emerald-600 px-3 py-1 text-[11px] font-semibold text-white shadow-sm cursor-default opacity-80"
                                       disabled
                                   >
                                       Marked
                                   </button>`
                : `<button
                                       type="button"
                                       class="inline-flex items-center rounded-lg bg-indigo-600 px-3 py-1 text-[11px] font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1"
                                       data-exam-id="${currentExamId}"
                                       data-student-id="${studentId}"
                                       data-mark-input-id="${inputId}"
                                       onclick="window.__ucmsSubmitExamMark && window.__ucmsSubmitExamMark(${currentExamId}, ${studentId})"
                                   >
                                       Submit
                                   </button>`
            }
                    </div>
                </td>
            </tr>
        `;
    });

    html += `
                </tbody>
            </table>
        </div>
    `;

    wrapper.innerHTML = html;
}

function setupExamModal() {
    const btnCreate = document.getElementById('btn-open-exam-modal');
    const modal = document.getElementById('exam-modal');
    const form = document.getElementById('exam-form');
    const btnClose = document.getElementById('exam-modal-close');
    const btnCancel = document.getElementById('exam-modal-cancel');

    if (!modal || !form) return;

    function openModal() {
        // ফর্ম reset
        form.reset();

        const totalMarksInput = document.getElementById('exam_total_marks');
        if (totalMarksInput && !totalMarksInput.value) {
            totalMarksInput.value = '100';
        }

        const dateInput = document.getElementById('exam_date');
        if (dateInput && !dateInput.value) {
            const today = new Date();
            const yyyy = today.getFullYear();
            const mm = String(today.getMonth() + 1).padStart(2, '0');
            const dd = String(today.getDate()).padStart(2, '0');
            dateInput.value = `${yyyy}-${mm}-${dd}`;
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeModal() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    // বাটন শুধু Teacher/Admin এর জন্য visible
    if (btnCreate) {
        btnCreate.classList.remove('hidden');
        if (!btnCreate.dataset.bound) {
            btnCreate.dataset.bound = '1';
            btnCreate.addEventListener('click', openModal);
        }
    }

    if (btnClose && !btnClose.dataset.bound) {
        btnClose.dataset.bound = '1';
        btnClose.addEventListener('click', closeModal);
    }

    if (btnCancel && !btnCancel.dataset.bound) {
        btnCancel.dataset.bound = '1';
        btnCancel.addEventListener('click', (e) => {
            e.preventDefault();
            closeModal();
        });
    }

    if (form && !form.dataset.bound) {
        form.dataset.bound = '1';
        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            const titleEl = document.getElementById('exam_title');
            const typeEl = document.getElementById('exam_type');
            const dateEl = document.getElementById('exam_date');
            const totalEl = document.getElementById('exam_total_marks');
            const descEl = document.getElementById('exam_description');

            const title = titleEl?.value?.trim();
            const examType = typeEl?.value?.trim();
            const examDate = dateEl?.value;
            const totalMarks = totalEl?.value;
            const description = descEl?.value?.trim() || null;

            if (!title || !examType || !examDate || !totalMarks) {
                alert('Title, exam type, date এবং total marks সবগুলোই আবশ্যক।');
                return;
            }

            const payload = {
                title,
                exam_type: examType,
                exam_date: examDate,
                total_marks: totalMarks,
                description,
            };

            try {
                const res = await _apiRequest(`/v1/classes/${_classId}/exams`, {
                    method: 'POST',
                    body: JSON.stringify(payload),
                });

                const created = res.data || res;

                if (!Array.isArray(examsCache)) {
                    examsCache = [];
                }
                examsCache.push(created);

                renderExamOptions(); // dropdown refresh
                closeModal();

                alert('Exam created successfully.');

            } catch (err) {
                console.error('Failed to create exam', err);
                const msg = err?.data?.message ||
                    (err?.data?.errors && Object.values(err.data.errors)[0][0]) ||
                    'Failed to create exam.';
                alert(msg);
            }
        });
    }
}

// প্রতি রো Submit-এর জন্য গ্লোবাল হ্যান্ডলার (MarksController@store ব্যবহার)
if (!window.__ucmsSubmitExamMark) {
    window.__ucmsSubmitExamMark = async function (examId, studentId) {
        const inputId = `exam-mark-${examId}-${studentId}`;
        const el = document.getElementById(inputId);
        if (!el) return;

        const raw = el.value.trim();
        if (raw === '') {
            alert('Please enter marks before submitting.');
            return;
        }

        const value = Number(raw);
        if (Number.isNaN(value)) {
            alert('Marks must be a valid number.');
            return;
        }

        if (!_apiRequest) {
            alert('Marks API is not ready yet.');
            return;
        }

        try {
            // MarksController@store → expects: { marks: [ { student_id, marks_obtained } ] }
            await _apiRequest(`/v1/exams/${examId}/marks`, {
                method: 'POST',
                body: JSON.stringify({
                    marks: [
                        {
                            student_id: studentId,
                            marks_obtained: value,
                        },
                    ],
                }),
            });

            // সফল হলে আবার marks reload করব → বাটন "Marked", input disabled
            await loadExamMarksForTeacher(examId);
        } catch (e) {
            console.error('Failed to submit mark', e);
            const msg = e?.data?.message || 'Failed to submit marks. Please try again.';
            alert(msg);
        }
    };
}

// ---------- Student side ----------

async function loadStudentMarks() {
    const wrapper = document.getElementById('student-marks-table-wrapper');
    if (wrapper) {
        wrapper.innerHTML = '<p class="text-sm text-slate-500">Loading your marks...</p>';
    }

    let rows = [];
    try {
        const res = await _apiRequest(`/v1/classes/${_classId}/my-marks`);
        rows = Array.isArray(res.data) ? res.data : (Array.isArray(res) ? res : []);
    } catch (e) {
        console.error('Failed to load my-marks', e);
        rows = [];
    }

    renderStudentMarksTable(rows);
}

function renderStudentMarksTable(rows) {
    const wrapper = document.getElementById('student-marks-table-wrapper');
    if (!wrapper) return;

    if (!rows.length) {
        wrapper.innerHTML = '<p class="text-sm text-slate-500">No marks available yet.</p>';
        return;
    }

    let html = `
        <div class="rounded-2xl border border-slate-200 overflow-hidden">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-3 py-2 text-left text-[11px] font-semibold tracking-wide text-slate-600 uppercase">
                            Exam Name
                        </th>
                        <th class="px-3 py-2 text-left text-[11px] font-semibold tracking-wide text-slate-600 uppercase">
                            Marks
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
    `;

    rows.forEach(row => {
        const title = row.title || row.exam_title || 'Exam';
        const obtained = row.marks_obtained != null ? row.marks_obtained : '-';
        const total = row.total_marks != null ? row.total_marks : '-';

        html += `
            <tr class="odd:bg-slate-50 hover:bg-slate-100/60 transition-colors">
                <td class="px-3 py-2 align-middle text-xs text-slate-700">
                    ${title}
                </td>
                <td class="px-3 py-2 align-middle text-xs text-slate-800">
                    ${obtained} / ${total}
                </td>
            </tr>
        `;
    });

    html += `
                </tbody>
            </table>
        </div>
    `;

    wrapper.innerHTML = html;
}
