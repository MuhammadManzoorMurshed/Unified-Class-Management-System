// resources/js/dashboard.js
console.log('UCMS >>> dashboard.js LOADED');

export function initDashboardPage({ apiRequest } = {}) {
    try {
        // 🔹 page-id detect: body[data-page] অথবা [data-page-id]
        const bodyPage =
            document.body?.dataset?.page ||
            document.body?.getAttribute('data-page') ||
            null;

        const root = document.querySelector('[data-page-id]');
        const pageIdAttr =
            root?.getAttribute('data-page-id') ||
            document.body?.dataset?.pageId ||
            null;

        const pageId = pageIdAttr || bodyPage;

        // এই পেজটা Dashboard না হলে কিছুই করব না
        if (pageId !== 'dashboard') {
            return;
        }

        if (typeof apiRequest !== 'function') {
            console.warn(
                'initDashboardPage: apiRequest missing. Dynamic dashboard disabled.'
            );
            return;
        }

        // --- DOM element references ---
        const elActiveClasses = document.getElementById('dash-active-classes');
        const elPendingAssignments = document.getElementById(
            'dash-due-assignments'
        );
        const elUpcomingExams = document.getElementById(
            'dash-upcoming-exams'
        );
        const elUnreadMessages = document.getElementById(
            'dash-unread-messages'
        );

        const elDeadlinesWrapper =
            document.getElementById('upcoming-deadlines');
        const elRecentWrapper = document.getElementById('recent-activity');

        // API call
        apiRequest('/v1/dashboard')
            .then((res) => {
                const data = res?.data || res || {};
                const stats = data.stats || {};
                const deadlines = Array.isArray(data.upcoming_deadlines)
                    ? data.upcoming_deadlines
                    : [];
                const activities = Array.isArray(data.recent_activities)
                    ? data.recent_activities
                    : [];

                // ---------- Stats ----------
                if (elActiveClasses) {
                    elActiveClasses.textContent =
                        stats.active_classes ?? 0;
                }
                if (elPendingAssignments) {
                    elPendingAssignments.textContent =
                        stats.pending_assignments ?? 0;
                }
                if (elUpcomingExams) {
                    elUpcomingExams.textContent =
                        stats.upcoming_exams ?? 0;
                }
                if (elUnreadMessages) {
                    elUnreadMessages.textContent =
                        stats.unread_messages ?? 0;
                }

                // ---------- Upcoming Deadlines ----------
                if (elDeadlinesWrapper) {
                    if (!deadlines.length) {
                        elDeadlinesWrapper.innerHTML = `
                            <div class="text-center py-8 text-slate-400">
                                <p class="text-sm">No upcoming deadlines.</p>
                            </div>
                        `;
                    } else {
                        elDeadlinesWrapper.innerHTML = deadlines
                            .map((item) => {
                                const title =
                                    item.title || 'Untitled item';
                                const course =
                                    item.course || 'Unknown course';
                                const dueLabel =
                                    item.due_label || 'Due soon';
                                const type =
                                    item.type || 'task';

                                const shortType = String(type)
                                    .slice(0, 3)
                                    .toUpperCase();

                                return `
                                    <div
                                        class="flex items-center justify-between p-3 bg-slate-50 rounded-lg border border-slate-200 hover:border-slate-300 transition">
                                        <div class="flex items-center space-x-3">
                                            <div
                                                class="w-8 h-8 rounded-lg bg-slate-200 flex items-center justify-center text-[10px] font-semibold text-slate-700">
                                                ${shortType}
                                            </div>
                                            <div>
                                                <div class="text-sm font-medium text-slate-800">
                                                    ${escapeHtml(title)}
                                                </div>
                                                <div class="text-xs text-slate-500">
                                                    ${escapeHtml(course)}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="text-xs font-medium text-red-600 bg-red-50 px-2 py-1 rounded">
                                            ${escapeHtml(dueLabel)}
                                        </div>
                                    </div>
                                `;
                            })
                            .join('');
                    }
                }

                // ---------- Recent Activities ----------
                if (elRecentWrapper) {
                    if (!activities.length) {
                        elRecentWrapper.innerHTML = `
                            <div class="text-center py-8 text-slate-400">
                                <p class="text-sm">No recent activity.</p>
                            </div>
                        `;
                    } else {
                        elRecentWrapper.innerHTML = activities
                            .map((a) => {
                                const action =
                                    a.action || 'Activity';
                                const course =
                                    a.course || 'Unknown course';
                                const timeLabel = a.time_label || '';
                                const type =
                                    a.type || 'item';

                                const shortType = String(type)
                                    .slice(0, 3)
                                    .toUpperCase();

                                return `
                                    <div
                                        class="flex items-center space-x-3 p-3 bg-slate-50 rounded-lg border border-slate-200 hover:border-slate-300 transition">
                                        <div
                                            class="w-8 h-8 rounded-lg bg-slate-200 flex items-center justify-center text-[10px] font-semibold text-slate-700">
                                            ${shortType}
                                        </div>
                                        <div class="flex-1">
                                            <div class="text-sm font-medium text-slate-800">
                                                ${escapeHtml(action)}
                                            </div>
                                            <div class="text-xs text-slate-500">
                                                ${escapeHtml(course)}
                                                ${timeLabel
                                        ? ' • ' + escapeHtml(timeLabel)
                                        : ''
                                    }
                                            </div>
                                        </div>
                                    </div>
                                `;
                            })
                            .join('');
                    }
                }
            })
            .catch((err) => {
                console.error('Dashboard API error', err);
            });
    } catch (e) {
        console.error('initDashboardPage crashed:', e);
    }
}

function escapeHtml(str) {
    if (str == null) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');
}
