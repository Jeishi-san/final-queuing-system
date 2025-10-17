    {{-- resources/views/layouts/navigation.blade.php --}}
    <nav class="bg-gray-800 text-white p-4 flex justify-between items-center shadow-md">
        <!-- Left side -->
        <div class="flex space-x-6">
            @auth
                <a href="{{ route('dashboard') }}" 
                class="hover:text-blue-400 font-semibold transition">
                    🎟️ Ticket Dashboard
                </a>
            @endauth
        </div>

        <!-- Right side -->
        <div class="flex items-center space-x-6 relative">
            @auth
                <!-- 🔔 Notification Bell -->
                <div class="relative" id="notificationWrapper">
                    <button id="notificationButton" 
                            class="relative focus:outline-none hover:text-blue-400 transition">
                        <!-- Bell Icon -->
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 17h5l-1.405-1.405C18.195 14.79 18 13.918 18 13V8a6 
                                    6 0 10-12 0v5c0 .918-.195 1.79-.595 2.595L4 17h5m6 
                                    0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>

                        <!-- 🔴 Badge -->
                        <span id="notificationBadge"
                            class="absolute -top-1 -right-1 bg-red-600 text-white text-xs font-bold rounded-full px-1.5 py-0.5 hidden">
                            0
                        </span>
                    </button>

                    <!-- Dropdown -->
                    <div id="notificationDropdown"
                        class="hidden absolute right-0 mt-3 w-80 bg-white text-gray-800 rounded-lg shadow-lg overflow-hidden z-50 border border-gray-200">
                        <div class="bg-gray-100 px-4 py-2 font-semibold text-gray-700 flex justify-between items-center">
                            <span>Notifications</span>
                            <span id="notificationCount"
                                class="text-xs bg-blue-600 text-white px-2 py-1 rounded-full">0</span>
                        </div>

                        <div id="notificationLoading" class="hidden text-center py-4">
                            <div class="inline-block animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
                        </div>

                        <ul id="notificationList" class="max-h-64 overflow-y-auto divide-y divide-gray-200">
                            <li class="text-center text-gray-500 text-sm py-3">Loading notifications...</li>
                        </ul>

                        <div class="bg-gray-50 px-4 py-2 text-sm text-center border-t border-gray-200">
                            <button id="clearNotifications"
                                    class="text-blue-600 hover:underline disabled:opacity-50" disabled>
                                Clear All
                            </button>
                        </div>
                    </div>
                </div>

                <!-- 👤 User Profile -->
                <a href="{{ route('profile') }}" class="hover:text-blue-400 text-sm">
                    {{ Auth::user()->name }}
                </a>

                <!-- 🚪 Logout -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="hover:text-blue-400 text-sm font-medium">
                        Logout
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="hover:text-blue-400">Login</a>
                <a href="{{ route('register') }}" class="hover:text-blue-400">Register</a>
            @endauth
        </div>
    </nav>

    {{-- ✅ Integrated Notification Logic (from index.blade.php, optimized) --}}
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const btn = document.getElementById('notificationButton');
        const dropdown = document.getElementById('notificationDropdown');
        const badge = document.getElementById('notificationBadge');
        const list = document.getElementById('notificationList');
        const clearBtn = document.getElementById('clearNotifications');
        const loading = document.getElementById('notificationLoading');
        const count = document.getElementById('notificationCount');

        let notifications = [];
        let loaded = false;
        let refreshing = false;

        btn?.addEventListener('click', async () => {
            dropdown.classList.toggle('hidden');
            if (!loaded && !dropdown.classList.contains('hidden')) {
                await loadNotifications();
            }
        });

        document.addEventListener('click', (e) => {
            if (!btn.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.classList.add('hidden');
            }
        });

        clearBtn?.addEventListener('click', async () => {
            if (!notifications.length) return;
            clearBtn.disabled = true;
            try {
                const res = await fetch('{{ route("notifications.clear") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                    }
                });
                if (res.ok) {
                    notifications = [];
                    renderNotifications();
                    showToast('✅ Notifications cleared');
                } else throw new Error();
            } catch {
                showToast('❌ Failed to clear notifications', 'error');
            } finally {
                clearBtn.disabled = false;
            }
        });

        async function loadNotifications() {
            loading.classList.remove('hidden');
            list.classList.add('hidden');
            try {
                const res = await fetch('{{ route("notifications.index") }}');
                const data = await res.json();
                notifications = data.notifications || [];
                loaded = true;
            } catch {
                notifications = [];
            } finally {
                renderNotifications();
                loading.classList.add('hidden');
                list.classList.remove('hidden');
            }
        }

        function renderNotifications() {
            list.innerHTML = notifications.length
                ? ''
                : `<li class="text-center text-gray-500 text-sm py-3">No notifications</li>`;

            let unread = 0;
            notifications.forEach(n => {
                const li = document.createElement('li');
                const isUnread = !n.read_at;
                if (isUnread) unread++;

                li.className = `px-4 py-3 text-sm hover:bg-gray-50 cursor-pointer border-l-4 ${
                    isUnread ? 'border-blue-500 bg-blue-50' : 'border-transparent'
                }`;

                li.innerHTML = `
                    <div class="flex justify-between items-start mb-1">
                        <span class="font-medium text-gray-900">${escapeHtml(n.title || 'Notification')}</span>
                        <span class="text-xs text-gray-500">${formatTime(n.created_at)}</span>
                    </div>
                    <p class="text-gray-600 text-xs mb-1">${escapeHtml(n.message || '')}</p>
                    ${n.ticket_number ? `<div class="text-xs text-blue-600 font-semibold">Ticket #${n.ticket_number}</div>` : ''}
                `;

                li.addEventListener('click', async () => {
                    if (!n.read_at) await markAsRead(n.id);
                    if (n.ticket_id) window.location.href = `{{ route('dashboard') }}?highlight=${n.ticket_id}`;
                });

                list.appendChild(li);
            });

            badge.classList.toggle('hidden', unread === 0);
            badge.textContent = unread;
            count.textContent = notifications.length;
            clearBtn.disabled = !notifications.length;
        }

        async function markAsRead(id) {
            try {
                await fetch(`/notifications/${id}/read`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                });
                const notif = notifications.find(n => n.id === id);
                if (notif) notif.read_at = new Date().toISOString();
                renderNotifications();
            } catch {}
        }

        setInterval(async () => {
            if (dropdown.classList.contains('hidden') || refreshing) return;
            refreshing = true;
            try {
                const res = await fetch('{{ route("notifications.index") }}?check_new=true');
                const data = await res.json();
                if (data.has_new) await loadNotifications();
            } finally {
                refreshing = false;
            }
        }, 30000);

        const escapeHtml = s => s?.replace(/[&<>"']/g, c => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
        }[c])) ?? '';

        const formatTime = date => {
            const d = new Date(date);
            const diff = (new Date() - d) / 60000;
            if (diff < 1) return 'just now';
            if (diff < 60) return `${Math.floor(diff)}m ago`;
            if (diff < 1440) return `${Math.floor(diff / 60)}h ago`;
            return d.toLocaleDateString();
        };

        const showToast = (msg, type = 'success') => {
            const el = document.createElement('div');
            el.className = `fixed bottom-4 right-4 px-4 py-2 rounded text-white ${
                type === 'success' ? 'bg-green-600' : 'bg-red-600'
            } shadow-md`;
            el.textContent = msg;
            document.body.appendChild(el);
            setTimeout(() => el.remove(), 2500);
        };

        // Auto-load when on dashboard
        if (window.location.pathname.includes('dashboard')) loadNotifications();
    });
    </script>
