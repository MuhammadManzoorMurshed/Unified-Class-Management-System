// resources/js/class-chats.js
console.log('UCMS >>> class-chats.js LOADED');

let _apiRequest = null;
let _classId = null;
let _currentUser = null;

let chatsCache = [];
let chatsInitialized = false;
let typingInitialized = false;
let typingHideTimeout = null;

// ================== PUBLIC INIT ==================

export function initChatsModule({ apiRequest, classId, currentUser }) {
    _apiRequest = apiRequest;
    _classId = classId;
    _currentUser = currentUser;

    // প্রথমবার ইনিশিয়ালাইজ
    if (!chatsInitialized) {
        chatsInitialized = true;
        setupChatForm();
        setupTypingIndicator();
    } else {
        // ট্যাবে ফিরে আসলে typing indicator আবার safe করতে
        setupTypingIndicator();
    }

    // যেকোনো ক্ষেত্রে নতুন করে মেসেজ লোড করব
    loadChats();
}

// ================== Typing Indicator ==================

function setupTypingIndicator() {
    // একাধিকবার addEventListener আটকানোর জন্য
    if (typingInitialized) return;
    typingInitialized = true;

    const input = document.getElementById('chat-input');
    const indicator = document.getElementById('chat-typing-indicator');
    if (!input || !indicator) return;

    input.addEventListener('input', () => {
        const hasText = input.value.trim().length > 0;

        if (!hasText) {
            indicator.classList.add('hidden');
            if (typingHideTimeout) clearTimeout(typingHideTimeout);
            return;
        }

        indicator.textContent = 'You are typing…';
        indicator.classList.remove('hidden');

        if (typingHideTimeout) clearTimeout(typingHideTimeout);
        typingHideTimeout = setTimeout(() => {
            indicator.classList.add('hidden');
        }, 2000);
    });
}

// ================== Load Chats ==================

async function loadChats() {
    const wrapper = document.getElementById('chat-messages-list');
    if (wrapper) {
        wrapper.innerHTML = `
            <p class="text-[11px] text-slate-500 text-center mt-6">
                Loading messages...
            </p>
        `;
    }

    try {
        const res = await _apiRequest(`/v1/classes/${_classId}/chats`);
        chatsCache = Array.isArray(res.data) ? res.data : (res || []);
        renderChats();
    } catch (e) {
        console.error('Failed to load chats', e);
        if (wrapper) {
            wrapper.innerHTML = `
                <p class="text-[11px] text-rose-500 text-center mt-6">
                    Failed to load messages.
                </p>
            `;
        }
    }
}

// ================== Helpers: Initials + Grouping ==================

function getInitials(nameOrEmail) {
    if (!nameOrEmail) return '?';
    const base = String(nameOrEmail).trim();
    if (!base) return '?';

    const namePart = base.includes('@') ? base.split('@')[0] : base;
    const parts = namePart.split(/\s+/).filter(Boolean);
    if (parts.length === 1) return parts[0].slice(0, 2).toUpperCase();
    return (parts[0][0] + parts[1][0]).toUpperCase();
}

function groupMessagesBySender(list, myId) {
    const groups = [];
    let lastGroup = null;

    const sorted = [...list].sort((a, b) => {
        const ta = new Date(a.created_at || a.createdAt || 0).getTime();
        const tb = new Date(b.created_at || b.createdAt || 0).getTime();
        return ta - tb;
    });

    sorted.forEach((m) => {
        const senderId = Number(m.sender_id);
        const isMine = senderId === Number(myId);
        const sender = m.sender || {};

        const name =
            sender.name ||
            sender.email ||
            (isMine ? 'You' : `User #${m.sender_id}`);

        if (lastGroup && lastGroup.senderId === senderId) {
            lastGroup.messages.push(m);
        } else {
            lastGroup = {
                senderId,
                isMine,
                name,
                avatar: getInitials(sender.name || sender.email || name),
                messages: [m],
            };
            groups.push(lastGroup);
        }
    });

    return groups;
}

// ------------------ Render Chats (simple, stable alignment) ------------------

function renderChats() {
    const wrapper = document.getElementById('chat-messages-list');
    const outer = document.getElementById('chat-messages-wrapper');
    if (!wrapper) return;

    // কোনো মেসেজ না থাকলে
    if (!Array.isArray(chatsCache) || chatsCache.length === 0) {
        wrapper.innerHTML = `
            <p class="text-[11px] text-slate-500 text-center mt-6">
                No messages yet. Start the conversation.
            </p>
        `;
        if (outer) outer.scrollTop = outer.scrollHeight;
        return;
    }

    const myId = _currentUser?.id;

    // সময় অনুযায়ী sort
    const sorted = [...chatsCache].sort((a, b) => {
        const ta = new Date(a.created_at || a.createdAt || 0).getTime();
        const tb = new Date(b.created_at || b.createdAt || 0).getTime();
        return ta - tb;
    });

    const html = sorted
        .map((m) => {
            const isMine = Number(m.sender_id) === Number(myId);
            const sender = m.sender || {};

            const name =
                sender.name ||
                sender.email ||
                (isMine ? 'You' : `User #${m.sender_id}`);

            const avatar = getInitials(sender.name || sender.email || name);
            const time = formatChatTime(m.created_at);

            const rowClass = isMine
                ? 'flex w-full justify-end mb-3'
                : 'flex w-full justify-start mb-3';

            const avatarHtml = `
                <div class="flex-shrink-0 w-7 h-7 rounded-full flex items-center justify-center text-[10px] font-semibold 
                            ${isMine ? 'bg-white text-indigo-600 border border-indigo-200 ml-2' : 'bg-indigo-600 text-white mr-2'}">
                    ${escapeHtml(avatar)}
                </div>
            `;

            const bubbleBase =
                'inline-block px-3 py-2 text-xs shadow-sm break-words';

            const bubbleClass = isMine
                ? `${bubbleBase} bg-indigo-600 text-white rounded-2xl rounded-br-sm`
                : `${bubbleBase} bg-white border border-slate-200 text-slate-800 rounded-2xl rounded-bl-sm`;

            return `
                <div class="${rowClass}">
                    ${isMine ? '' : avatarHtml}
                    <div class="flex flex-col ${isMine ? 'items-end' : 'items-start'} max-w-[80%]">
                        <div class="text-[10px] text-slate-400 mb-1 ${isMine ? 'text-right' : 'text-left'}">
                            ${escapeHtml(name)}
                        </div>

                        <div class="${bubbleClass}">
                            ${escapeHtml(m.content ?? m.message ?? '')}
                        </div>

                        <div class="mt-0.5 text-[9px] text-slate-400 ${isMine ? 'text-right' : 'text-left'}">
                            ${time || ''}
                        </div>
                    </div>
                    ${isMine ? avatarHtml : ''}
                </div>
            `;
        })
        .join('');

    const wasNearBottom =
        outer &&
        outer.scrollHeight - outer.scrollTop - outer.clientHeight < 80;

    wrapper.innerHTML = html;

    if (outer && wasNearBottom) {
        outer.scrollTo({
            top: outer.scrollHeight,
            behavior: 'smooth',
        });
    }
}


// ================== Chat Form (send message) ==================

function setupChatForm() {
    const form = document.getElementById('chat-form');
    const input = document.getElementById('chat-input');
    const sendBtn = document.getElementById('chat-send-btn');
    const sendText = document.getElementById('chat-send-text');
    const errorEl = document.getElementById('chat-error');
    const indicator = document.getElementById('chat-typing-indicator');

    if (!form || !input || !sendBtn) return;

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        if (errorEl) {
            errorEl.classList.add('hidden');
            errorEl.textContent = '';
        }

        const text = (input.value || '').trim();
        if (!text) return;

        sendBtn.disabled = true;
        if (sendText) sendText.textContent = 'Sending...';

        try {
            const res = await _apiRequest(`/v1/classes/${_classId}/chats`, {
                method: 'POST',
                body: JSON.stringify({ message: text }),
            });

            const newMsg = res.data || res;
            chatsCache.push(newMsg);
            renderChats();

            // ইনপুট reset + typing indicator off
            input.value = '';
            if (indicator) {
                indicator.classList.add('hidden');
            }
            if (typingHideTimeout) {
                clearTimeout(typingHideTimeout);
                typingHideTimeout = null;
            }
        } catch (err) {
            console.error('Send error', err);
            if (errorEl) {
                errorEl.textContent =
                    err?.data?.message || 'Failed to send message.';
                errorEl.classList.remove('hidden');
            }
        } finally {
            sendBtn.disabled = false;
            if (sendText) sendText.textContent = 'Send';
        }
    });
}

// ================== Utility Helpers ==================

function escapeHtml(str) {
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');
}

function formatChatTime(dateStr) {
    if (!dateStr) return '';
    const d = new Date(dateStr);
    if (Number.isNaN(d.getTime())) return '';
    const hh = String(d.getHours()).padStart(2, '0');
    const mm = String(d.getMinutes()).padStart(2, '0');
    return `${hh}:${mm}`;
}
