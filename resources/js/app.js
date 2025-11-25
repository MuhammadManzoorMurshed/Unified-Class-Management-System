import './bootstrap';
const API_BASE = '/api';

function getToken() {
    return localStorage.getItem('ucms_token');
}

function setToken(token) {
    localStorage.setItem('ucms_token', token);
}

function clearToken() {
    localStorage.removeItem('ucms_token');
}

async function apiRequest(path, options = {}) {
    const token = getToken();

    const headers = Object.assign(
        { 'Accept': 'application/json' },
        options.headers || {}
    );

    // JSON body হলে Content-Type সেট করব
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

document.addEventListener('DOMContentLoaded', () => {
    const page = document.body.dataset.page;

    // ⭐ Auth pages → token লাগবে না
    const authPages = [
        'auth.login',
        'auth.register',
        'auth.verify-otp'
    ];

    // যদি auth পেজ হয় → token validation skip
    if (authPages.includes(page)) {
        if (page === 'auth.login') initLoginPage();
        if (page === 'auth.register') initRegisterPage();
        if (page === 'auth.verify-otp') initVerifyOtpPage();
        return;
    }

    // ⭐ App protected pages → token না থাকলে login
    if (!getToken()) {
        window.location.href = '/login';
        return;
    }

    // Load profile
    loadProfileIntoNav().catch(() => {
        clearToken();
        window.location.href = '/login';
    });

    // Page-specific init
    if (page === 'dashboard') {
        initDashboardPage();
    } else if (page === 'classes.index') {
        initClassesIndexPage();
    } else if (page === 'classes.show') {
        initClassShowPage();
    }
});


// ============ Login Page ============
function initLoginPage() {
    const form = document.getElementById('login-form');
    const errorBox = document.getElementById('login-error');
    const btnText = document.getElementById('login-btn-text');
    const btnSpinner = document.getElementById('login-btn-spinner');

    if (!form) return;

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        errorBox.classList.add('hidden');
        btnText.textContent = 'Signing in';
        btnSpinner.classList.remove('hidden');

        const formData = new FormData(form);
        const payload = {
            email: formData.get('email'),
            password: formData.get('password'),
        };

        try {
            const res = await apiRequest('/auth/login', {
                method: 'POST',
                body: JSON.stringify(payload),
            });

            // এখানে তোমার AuthController@login রেসপন্স ফরম্যাট অনুযায়ী adjust করো
            // ধরলাম: { token: "...", user: {...} }
            if (res.token) {
                setToken(res.token);
                window.location.href = '/dashboard';
            } else {
                throw { data: { message: 'Invalid response format' } };
            }
        } catch (err) {
            console.error(err);
            const msg =
                err?.data?.message ||
                err?.data?.errors?.email?.[0] ||
                'Login failed.';
            errorBox.textContent = msg;
            errorBox.classList.remove('hidden');
        } finally {
            btnText.textContent = 'Sign in';
            btnSpinner.classList.add('hidden');
        }
    });
}

// ============ Registration Page ============
function initRegisterPage() {
    const form = document.getElementById('register-form');
    const errorBox = document.getElementById('register-error');
    const successBox = document.getElementById('register-success');
    const btnText = document.getElementById('register-btn-text');
    const btnSpinner = document.getElementById('register-btn-spinner');

    const pwdInput = document.getElementById('reg-password-input');
    const toggleBtn = document.getElementById('reg-toggle-password');
    if (pwdInput && toggleBtn) {
        toggleBtn.addEventListener('click', () => {
            const isHidden = pwdInput.type === 'password';
            pwdInput.type = isHidden ? 'text' : 'password';
            toggleBtn.textContent = isHidden ? '👁️' : '👁️‍🗨️';
        });
    }

    if (!form) return;

    // যদি already logged in থাকে, সরাসরি dashboard
    if (getToken()) {
        window.location.href = '/dashboard';
        return;
    }

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        errorBox.classList.add('hidden');
        successBox.classList.add('hidden');

        btnText.textContent = 'Registering...';
        btnSpinner.classList.remove('hidden');

        const fd = new FormData(form);
        const payload = {
            name: fd.get('name'),
            email: fd.get('email'),
            password: fd.get('password'),
            password_confirmation: fd.get('password_confirmation'),
            // যদি তোমার register API-তে role/purpose লাগে, এখানে add করতে পারো
            // role: 'Student'
        };

        try {
            const res = await apiRequest('/auth/register', {
                method: 'POST',
                body: JSON.stringify(payload),
            });

            const email = payload.email;
            localStorage.setItem('ucms_pending_email', email);
            if (res.token) {
                localStorage.setItem('ucms_verify_token', res.token);
            }

            successBox.textContent =
                res?.message || 'Registration successful. Please verify OTP.';
            successBox.classList.remove('hidden');

            setTimeout(() => {
                window.location.href = '/verify-otp';
            }, 800);
        } catch (err) {
            console.error(err);
            const msg =
                err?.data?.message ||
                (err?.data?.errors && Object.values(err.data.errors)[0][0]) ||
                'Registration failed.';
            errorBox.textContent = msg;
            errorBox.classList.remove('hidden');
        } finally {
            btnText.textContent = 'Register';
            btnSpinner.classList.add('hidden');
        }
    });
}

// ============ Verify OTP Page ============
function initVerifyOtpPage() {
    const form = document.getElementById('otp-form');
    const errorBox = document.getElementById('otp-error');
    const successBox = document.getElementById('otp-success');
    const btnText = document.getElementById('otp-btn-text');
    const btnSpinner = document.getElementById('otp-btn-spinner');
    const emailDisplay = document.getElementById('otp-email-display');
    const resendBtn = document.getElementById('btn-resend-otp');

    if (!form) return;

    const pendingEmail = localStorage.getItem('ucms_pending_email');
    const verifyToken = localStorage.getItem('ucms_verify_token');

    if (!pendingEmail) {
        emailDisplay.textContent = 'No email found. Please login or register again.';
    } else {
        emailDisplay.textContent = pendingEmail;
    }

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        errorBox.classList.add('hidden');
        successBox.classList.add('hidden');

        btnText.textContent = 'Verifying...';
        btnSpinner.classList.remove('hidden');

        const fd = new FormData(form);
        const payload = {
            token: verifyToken,          // 🔴 এখানে token যাচ্ছে
            otp: fd.get('otp'),         // 🔴 OTP
            // email লাগলে (backend এ দরকার হলে) extra হিসেবে পাঠাতে পারো:
            // email: pendingEmail,
        };

        try {
            const res = await apiRequest('/auth/verify-otp', {
                method: 'POST',
                body: JSON.stringify(payload),
            });

            successBox.textContent =
                res?.message || 'OTP verified successfully.';
            successBox.classList.remove('hidden');

            // verifyOtp এখন কোনো JWT token দেয় না, শুধু email verify করে
            // তাই সবসময় login এ রিডাইরেক্ট করা নিরাপদ
            localStorage.removeItem('ucms_pending_email');
            localStorage.removeItem('ucms_verify_token');

            setTimeout(() => {
                window.location.href = '/login';
            }, 800);

        } catch (err) {
            console.error(err);
            const msg =
                err?.data?.message ||
                (err?.data?.errors && Object.values(err.data.errors)[0][0]) ||
                'OTP verification failed.';
            errorBox.textContent = msg;
            errorBox.classList.remove('hidden');
        } finally {
            btnText.textContent = 'Verify';
            btnSpinner.classList.add('hidden');
        }
    });

    if (resendBtn) {
        resendBtn.addEventListener('click', async () => {
            if (!pendingEmail) {
                alert('No email found to resend OTP.');
                return;
            }

            try {
                const res = await apiRequest('/auth/resend-otp', {
                    method: 'POST',
                    body: JSON.stringify({ email: pendingEmail }),
                });

                successBox.textContent =
                    res?.message || 'OTP resent successfully.';
                successBox.classList.remove('hidden');

            } catch (err) {
                console.error(err);
                const msg =
                    err?.data?.message ||
                    'Failed to resend OTP.';
                errorBox.textContent = msg;
                errorBox.classList.remove('hidden');
            }
        });
    }
}

// ============ Profile load (nav + dashboard) ============
async function loadProfileIntoNav() {
    const res = await apiRequest('/v1/me');
    const user = res.user || res;

    // Dashboard fill
    const dashName = document.getElementById('dash-name');
    const dashRole = document.getElementById('dash-role');
    const dashEmail = document.getElementById('dash-email');

    if (dashName) {
        dashName.textContent = user.name || user.email;
    }
    if (dashRole) {
        dashRole.textContent = user.role?.role_name || user.role_name || 'N/A';
    }
    if (dashEmail) {
        dashEmail.textContent = user.email || '';
    }

    // Role-based section show
    const rawRole = user.role || user.role_name || null;
    const role = rawRole ? rawRole.toLowerCase() : '';

    if (role === 'admin' || role === 'teacher') {
        const sec = document.getElementById('dash-admin-teacher');
        if (sec) sec.classList.remove('hidden');
    } else if (role === 'student') {
        const sec = document.getElementById('dash-student');
        if (sec) sec.classList.remove('hidden');
    }

    // 🔹 এখানেই Create Class বাটন শো/হাইড করব
    const createBtn = document.getElementById('btn-create-class');
    if (createBtn) {
        if (role === 'admin' || role === 'teacher') {
            createBtn.classList.remove('hidden');
        } else {
            createBtn.classList.add('hidden');
        }
    }

    // Dashboard loading state
    const dashLoading = document.getElementById('dashboard-loading');
    const dashContent = document.getElementById('dashboard-content');
    if (dashLoading && dashContent) {
        dashLoading.classList.add('hidden');
        dashContent.classList.remove('hidden');
    }

    return user;
}


function initDashboardPage() {
    // চাইলে এখানে পরে quick stats-এর জন্য API কল যোগ করব
}

// ============ My Classes Page ============
function initClassesIndexPage() {
    const listEl = document.getElementById('classes-list');
    const emptyEl = document.getElementById('classes-empty');
    const countEl = document.getElementById('classes-count');
    const joinForm = document.getElementById('join-class-form');

    // ---- 1) Class card renderer ----
    function renderModernClassCard(c) {
        const codeLabel = (c.code || 'CLS').substring(0, 3).toUpperCase();
        const isArchived = c.is_archived || c.archived || false;

        return `
            <div class="group bg-white rounded-2xl shadow-sm border border-slate-200/70 hover:shadow-lg hover:border-slate-300 transition-all duration-300 cursor-pointer overflow-hidden transform hover:-translate-y-1">
                <div class="h-1.5 w-full bg-gradient-to-r from-indigo-500 to-purple-600"></div>
                <div class="p-6 space-y-5">
                    <div class="flex items-start justify-between">
                        <div class="w-12 h-12 rounded-xl bg-indigo-50 border border-indigo-200 flex items-center justify-center font-bold text-indigo-600 text-sm">
                            ${codeLabel}
                        </div>
                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold ${isArchived ? 'bg-slate-100 text-slate-700' : 'bg-emerald-100 text-emerald-700'} flex items-center gap-1">
                            <span class="w-2 h-2 ${isArchived ? 'bg-slate-500' : 'bg-emerald-500'} rounded-full ${!isArchived ? 'animate-pulse' : ''}"></span>
                            ${isArchived ? 'Archived' : 'Active'}
                        </span>
                    </div>

                    <div class="space-y-2">
                        <h3 class="font-bold text-slate-800 text-lg leading-snug group-hover:text-indigo-600 transition-colors line-clamp-2">
                            ${c.name || c.title || 'Untitled Class'}
                        </h3>
                        <p class="text-slate-600 text-sm leading-relaxed line-clamp-2">
                            ${c.description || 'No description provided.'}
                        </p>
                        <div class="flex items-center gap-2 text-xs text-slate-500 mt-1">
                            <span class="font-mono bg-slate-100 px-2 py-1 rounded-lg border border-slate-200">
                                ${c.code || '-'}
                            </span>
                            <span class="w-1 h-1 bg-slate-300 rounded-full"></span>
                            <span class="truncate">${c.teacher?.name || c.teacher_name || c.teacher || 'Unknown teacher'}</span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                        <div class="flex items-center gap-4 text-xs text-slate-500">
                            <div class="flex items-center gap-1.5" title="${c.member_count || c.members_count || 0} members">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <span class="font-semibold text-slate-700">${c.member_count || c.members_count || 0}</span>
                            </div>
                            <div class="flex items-center gap-1.5" title="${c.assignments_count || c.assignments || 0} assignments">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <span class="font-semibold text-slate-700">${c.assignments_count || c.assignments || 0}</span>
                            </div>
                        </div>
                        <span class="px-3 py-1.5 bg-slate-100 text-slate-700 rounded-lg text-xs font-semibold border border-slate-200">
                            ${c.semester || c.session || 'N/A'}
                        </span>
                    </div>
                </div>
            </div>
        `;
    }

    // ---- 2) Load classes from /api/v1/my-classes ----
    apiRequest('/v1/my-classes')
        .then((res) => {
            const classes = res.data || res;

            if (!classes || classes.length === 0) {
                if (countEl) {
                    countEl.innerHTML = `<span class="font-semibold text-slate-700">0</span> classes in your list`;
                }
                return;
            }

            if (emptyEl) emptyEl.classList.add('hidden');
            if (listEl) {
                listEl.classList.remove('hidden');
                listEl.innerHTML = '';

                classes.forEach((c) => {
                    const cardWrapper = document.createElement('a');
                    cardWrapper.href = `/classes/${c.id}`;
                    cardWrapper.className = 'block';
                    cardWrapper.innerHTML = renderModernClassCard(c);
                    listEl.appendChild(cardWrapper);
                });

                if (countEl) {
                    countEl.innerHTML =
                        `<span class="font-semibold text-slate-700">${classes.length}</span> classes in your list`;
                }
            }
        })
        .catch((err) => {
            console.error(err);
        });

    // ---- 3) Join class ----
    if (joinForm) {
        joinForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const fd = new FormData(joinForm);
            const code = (fd.get('code') || '').trim();

            if (!code) return;

            try {
                await apiRequest('/v1/classes/join', {
                    method: 'POST',
                    body: JSON.stringify({ code }),
                });
                window.location.reload();
            } catch (err) {
                alert(err?.data?.message || 'Failed to join class.');
            }
        });
    }

    // ---- 4) Create Class modal & form ----
    const btnCreate = document.getElementById('btn-create-class');
    const modal = document.getElementById('create-class-modal');
    const closeBtn = document.getElementById('create-class-close');
    const cancelBtn = document.getElementById('create-class-cancel');
    const createForm = document.getElementById('create-class-form');
    const errorBox = document.getElementById('create-class-error');
    const submitBtn = document.getElementById('create-class-submit');
    const submitText = document.getElementById('create-class-submit-text');
    const submitSpinner = document.getElementById('create-class-submit-spinner');

    function openModal() {
        if (!modal) return;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        if (errorBox) {
            errorBox.classList.add('hidden');
            errorBox.textContent = '';
        }
        if (createForm) createForm.reset();
    }

    function closeModal() {
        if (!modal) return;
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    if (btnCreate && modal) {
        btnCreate.addEventListener('click', openModal);
    }
    if (closeBtn) {
        closeBtn.addEventListener('click', closeModal);
    }
    if (cancelBtn) {
        cancelBtn.addEventListener('click', (e) => {
            e.preventDefault();
            closeModal();
        });
    }
    if (modal) {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                closeModal();
            }
        });
    }

    if (createForm) {
        createForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            if (errorBox) {
                errorBox.classList.add('hidden');
                errorBox.textContent = '';
            }

            const fd = new FormData(createForm);
            const payload = {
                title: (fd.get('title') || '').trim(),
                subject: (fd.get('subject') || '').trim(),
                session: (fd.get('session') || '').trim(), // ← API-তে session, DB-তে semester ম্যাপ করবে
                description: ((fd.get('description') || '').trim()) || null,
            };

            if (!payload.title || !payload.subject || !payload.session) {
                if (errorBox) {
                    errorBox.textContent = 'Title, subject and session are required.';
                    errorBox.classList.remove('hidden');
                }
                return;
            }

            if (submitBtn && submitText && submitSpinner) {
                submitBtn.disabled = true;
                submitText.textContent = 'Creating...';
                submitSpinner.classList.remove('hidden');
            }

            try {
                await apiRequest('/v1/classes', {
                    method: 'POST',
                    body: JSON.stringify(payload),
                });

                closeModal();
                window.location.reload();
            } catch (err) {
                console.error(err);
                let msg = err?.data?.message || 'Failed to create class.';

                if (err?.data?.errors) {
                    const firstError = Object.values(err.data.errors)[0];
                    if (Array.isArray(firstError) && firstError.length > 0) {
                        msg = firstError[0];
                    }
                }

                if (err?.status === 403) {
                    msg = 'You are not allowed to create classes. Only Admin or Teacher can create.';
                }

                if (errorBox) {
                    errorBox.textContent = msg;
                    errorBox.classList.remove('hidden');
                }
            } finally {
                if (submitBtn && submitText && submitSpinner) {
                    submitBtn.disabled = false;
                    submitText.textContent = 'Create';
                    submitSpinner.classList.add('hidden');
                }
            }
        });
    }
}


// ============ Class Workspace Page ============
function initClassShowPage() {
    import('./class-show.js')
        .then((module) => {
            if (typeof module.default === 'function') {
                module.default();
            }
        })
        .catch((err) => {
            console.error('Failed to load class-show.js', err);
        });
}


// // ============ Class Overview Page ============
// function initClassShowPage() {
//     const container = document.getElementById('class-page');
//     if (!container) return;

//     const classId = container.dataset.classId;

//     apiRequest(`/class/${classId}`)
//         .then((res) => {
//             const c = res.data || res;

//             document.getElementById('class-name').textContent = c.name || c.title || 'Untitled class';
//             document.getElementById('class-code').textContent = c.code || '-';
//             document.getElementById('class-description').textContent = c.description || 'No description.';
//             document.getElementById('class-subject').textContent = c.subject || '-';
//             document.getElementById('class-semester').textContent = c.semester || '-';
//             document.getElementById('class-year').textContent = c.year || '-';

//             const teacherEl = document.getElementById('class-teacher');
//             if (teacherEl && c.teacher) {
//                 teacherEl.textContent = c.teacher.name || c.teacher.email || '-';
//             }

//             const memberCountEl = document.getElementById('class-member-count');
//             if (memberCountEl && c.member_count !== undefined) {
//                 memberCountEl.textContent = c.member_count;
//             }
//         })
//         .catch((err) => {
//             console.error(err);
//             alert('Failed to load class details.');
//         });
// }

// Password show/hide
document.addEventListener('DOMContentLoaded', () => {
    const pwdInput = document.getElementById('password-input');
    const toggleBtn = document.getElementById('toggle-password');

    if (pwdInput && toggleBtn) {
        toggleBtn.addEventListener('click', () => {
            const isHidden = pwdInput.type === 'password';
            pwdInput.type = isHidden ? 'text' : 'password';
            toggleBtn.textContent = isHidden ? '👁️' : '👁️‍🗨️';
        });
    }
});

