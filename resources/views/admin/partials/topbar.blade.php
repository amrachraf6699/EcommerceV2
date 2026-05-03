<header class="border-b border-white/10 bg-slate-950/60 px-4 py-4 backdrop-blur sm:px-6 xl:px-10">
    <div class="admin-topbar-shell">
        <div class="admin-topbar-main">
            <div class="admin-topbar-actions">
                <button
                    type="button"
                    class="admin-topbar-menu-toggle xl:hidden"
                    data-sidebar-toggle
                    aria-label="فتح القائمة الجانبية"
                    aria-expanded="false"
                >
                    <i class="bx bx-menu text-2xl"></i>
                </button>

                <div class="admin-topbar-action-group" dir="rtl">
                    <a
                        href="{{ url('/') }}"
                        target="_blank"
                        rel="noreferrer"
                        class="admin-topbar-icon"
                        aria-label="عرض الموقع"
                        title="عرض الموقع"
                    >
                        <i class="bx bx-globe text-xl" aria-hidden="true"></i>
                    </a>

                    <details class="admin-topbar-dropdown">
                        <summary class="admin-topbar-icon admin-topbar-icon--notifications list-none" aria-label="الإشعارات" title="الإشعارات">
                            <i class="bx bx-bell text-xl" aria-hidden="true"></i>
                            @if ($unreadAdminNotificationsCount > 0)
                                <span class="admin-topbar-badge">{{ $unreadAdminNotificationsCount > 99 ? '99+' : $unreadAdminNotificationsCount }}</span>
                            @endif
                        </summary>

                        <div class="admin-topbar-menu" dir="rtl">
                            <div class="flex items-center justify-between gap-3 border-b border-black/10 px-4 py-3">
                                <p class="text-sm font-bold text-black">الإشعارات</p>

                                @if ($unreadAdminNotificationsCount > 0)
                                    <form method="POST" action="{{ route('admin.notifications.read-all') }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="text-xs font-bold text-neutral-600 transition hover:text-black">
                                            تعليم الكل كمقروء
                                        </button>
                                    </form>
                                @endif
                            </div>

                            @if ($adminNotifications->isEmpty())
                                <div class="px-4 py-3 text-sm text-neutral-600">
                                    لا توجد إشعارات حاليا.
                                </div>
                            @else
                                <div class="max-h-96 overflow-y-auto">
                                    @foreach ($adminNotifications as $notification)
                                        @php
                                            $notificationTitle = data_get($notification->data, 'title', 'إشعار جديد');
                                            $notificationBody = data_get($notification->data, 'body', '');
                                            $notificationUrl = data_get($notification->data, 'url');
                                        @endphp

                                        <div class="border-b border-black/10 last:border-b-0">
                                            <form method="POST" action="{{ route('admin.notifications.read', $notification) }}">
                                                @csrf
                                                @method('PATCH')

                                                <button type="submit" class="admin-notification-item {{ $notification->read_at ? 'is-read' : 'is-unread' }}">
                                                    @if ($notification->read_at === null)
                                                        <span class="admin-notification-dot" aria-hidden="true"></span>
                                                    @endif

                                                    <span class="block flex-1">
                                                        <span class="block text-sm font-bold text-black">{{ $notificationTitle }}</span>

                                                        @if ($notificationBody !== '')
                                                            <span class="mt-1 block text-xs leading-6 text-neutral-600">{{ $notificationBody }}</span>
                                                        @endif

                                                        <span class="mt-2 block text-[11px] text-neutral-500">
                                                            {{ $notification->created_at?->diffForHumans() }}
                                                            @if ($notificationUrl)
                                                                <span class="text-neutral-400">•</span>
                                                                <span>فتح</span>
                                                            @endif
                                                        </span>
                                                    </span>
                                                </button>
                                            </form>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </details>

                    @auth
                        <form method="POST" action="{{ route('admin.logout') }}" data-loading-form>
                            @csrf
                            <button
                                type="submit"
                                class="admin-topbar-logout"
                                data-loading-label="جارٍ تسجيل الخروج..."
                            >
                                تسجيل الخروج
                            </button>
                        </form>
                    @endauth
                </div>
            </div>

            <div data-topbar-breadcrumbs class="admin-topbar-breadcrumbs" dir="rtl">
                @foreach (($breadcrumbs ?? []) as $breadcrumb)
                    <span>{{ $breadcrumb }}</span>
                @endforeach
            </div>
        </div>
    </div>
</header>
