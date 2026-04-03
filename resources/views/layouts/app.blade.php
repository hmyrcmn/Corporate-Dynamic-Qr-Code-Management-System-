<!DOCTYPE html>
<html lang="tr" class="min-h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dinamik QR Yönetim Platformu')</title>
    <meta name="theme-color" content="#f4f7f8" id="theme-color-meta">
    <link rel="icon" type="image/png" href="{{ asset('img/yee-favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('img/yee-favicon.png') }}">
    <script>
        (function () {
            const theme = localStorage.getItem('dynamicqr-theme');
            const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            const activeTheme = theme === 'dark' || theme === 'light' ? theme : systemTheme;

            document.documentElement.dataset.theme = activeTheme;
            document.documentElement.classList.toggle('dark', activeTheme === 'dark');
            document.documentElement.style.colorScheme = activeTheme;
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="ui-lite min-h-screen overflow-x-hidden antialiased sf-text">
    <div class="ambient-bg">
        <div class="ambient-grid"></div>
        <div class="ambient-orb orb-a"></div>
        <div class="ambient-orb orb-b"></div>
        <div class="ambient-orb orb-c"></div>
    </div>

    @guest
        <header class="public-header-shell fixed inset-x-0 top-0 z-50 px-4 pt-3 md:px-6 md:pt-4 lg:px-8" data-public-header>
            <div class="mx-auto max-w-[68rem] page-card apple-glass-heavy rounded-[2rem] px-4 py-4 md:px-6 md:py-5 shadow-[0_20px_56px_rgba(53,116,125,0.1)]">
                <div class="flex items-center justify-between gap-3">
                    <a href="{{ route('landing') }}" class="inline-flex shrink-0 items-center">
                        <div class="logo-shell px-2 py-1 md:px-1 md:py-0">
                            <img src="{{ asset('img/yee-logo.png') }}" alt="Yunus Emre Enstitüsü logosu" class="brand-logo h-7 md:h-[3.35rem]">
                        </div>
                    </a>

                    <div class="hidden min-w-0 flex-1 justify-center md:flex">
                        <div class="min-w-0 px-2 py-1 text-center">
                            <p class="truncate text-[14px] font-black tracking-[-0.02em] text-brand-ink dark:text-white sf-display">Dinamik QR Yönetim Platformu</p>
                            <div class="mt-1 flex items-center justify-center gap-2">
                                <span class="truncate text-[10px] font-semibold text-slate-600 dark:text-slate-300">Yunus Emre Enstitüsü</span>
                                <span class="h-1 w-1 rounded-full bg-cyan-500/25"></span>
                                <span class="translate-y-px text-[8px] font-medium uppercase tracking-[0.28em] text-slate-400 dark:text-slate-500">Kurumsal Kullanım</span>
                            </div>
                        </div>
                    </div>

                    <nav class="flex items-center gap-3">
                        <button type="button" class="theme-toggle px-3.5 py-2.5 md:px-4 md:py-2.5" data-theme-toggle aria-label="Temayı değiştir">
                            <svg data-theme-icon="light" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v2m0 14v2m7-9h2M3 12H5m12.364 6.364 1.414 1.414M5.222 5.222l1.414 1.414m0 10.728-1.414 1.414m12.728-12.728-1.414 1.414M12 16a4 4 0 100-8 4 4 0 000 8z"></path>
                            </svg>
                            <svg data-theme-icon="dark" class="hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9 9 0 1012 21a8.96 8.96 0 008.354-5.646z"></path>
                            </svg>
                            <span data-theme-label class="hidden md:inline">Koyu tema</span>
                        </button>

                        <a href="{{ route('login') }}" class="btn-brand px-4 py-2.5 text-[13px] md:px-5 md:py-2.5">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3l7 4v5c0 5-3.5 8.5-7 9-3.5-.5-7-4-7-9V7l7-4z"></path>
                            </svg>
                            <span>Kurumsal Giriş</span>
                        </a>
                    </nav>
                </div>
            </div>
        </header>
    @endguest

    <div class="flex min-h-screen">
        @auth
            @php
                $authUser = auth()->user();
                $sidebarDepartment = request()->route('department');
                $hasGlobalDepartmentAccess = $authUser->hasGlobalDepartmentAccess();
                $sidebarContextLabel = $hasGlobalDepartmentAccess && $sidebarDepartment
                    ? $sidebarDepartment->name
                    : $authUser->departmentContextLabel();
                $newRecordUrl = $hasGlobalDepartmentAccess && $sidebarDepartment
                    ? route('qr.department.create', $sidebarDepartment)
                    : (! $hasGlobalDepartmentAccess ? route('qr.create') : null);
                $newRecordActive = $hasGlobalDepartmentAccess
                    ? request()->routeIs('qr.department.*')
                    : request()->routeIs('qr.create', 'qr.edit', 'qr.delete.confirm');
            @endphp
            <aside id="sidebar" data-sidebar class="sidebar-transition fixed inset-y-0 left-0 z-50 my-3 mr-3 ml-0 w-[16.25rem] -translate-x-[120%] rounded-[1.6rem] page-card apple-glass-heavy nav-shell lg:sticky lg:inset-y-auto lg:left-auto lg:top-6 lg:my-5 lg:mr-0 lg:ml-0 lg:h-[calc(100vh-2.5rem)] lg:w-[17.5rem] lg:translate-x-0 lg:self-start lg:rounded-[1.8rem]">
                <div class="flex h-full flex-col p-4 lg:p-5">
                    <a href="{{ route('dashboard') }}" class="mb-5 block">
                        <div class="logo-shell w-full justify-start px-1 py-1">
                            <img src="{{ asset('img/yee-logo.png') }}" alt="Yunus Emre Enstitüsü logosu" class="brand-logo h-11">
                        </div>
                        <div class="mt-4 px-1">
                            <div class="sidebar-intro">
                                <div class="sidebar-intro-copy">
                                    <span class="block truncate text-[0.78rem] font-semibold tracking-[0.01em] text-slate-500 dark:text-slate-400" title="{{ $sidebarContextLabel }}">
                                        {{ $sidebarContextLabel }}
                                    </span>
                                </div>
                                <div class="sidebar-intro-badge" aria-hidden="true">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.9" d="M12 3l7 4v5c0 5-3.5 8.5-7 9-3.5-.5-7-4-7-9V7l7-4z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.9" d="M10 12.5l1.5 1.5L14.5 11"></path>
                                    </svg>
                                </div>
                            </div>
                            <h1 class="mt-3 text-[1rem] font-semibold text-brand-ink dark:text-white">Dinamik QR Paneli</h1>
                            <p class="mt-2.5 text-[0.88rem] leading-7 text-slate-600 dark:text-slate-300">
                                Bağlantıları yönetin, QR dosyalarını üretin ve taramaları izleyin.
                            </p>
                        </div>
                    </a>

                    <nav class="space-y-2">
                        <a href="{{ route('dashboard') }}" class="nav-item-link {{ request()->routeIs('dashboard', 'dashboard.department') ? 'is-active' : '' }}">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                            </svg>
                            <span>{{ $hasGlobalDepartmentAccess ? 'Birimler' : 'Genel Bakış' }}</span>
                        </a>

                        @if ($newRecordUrl)
                            <a href="{{ $newRecordUrl }}" class="nav-item-link {{ $newRecordActive ? 'is-active' : '' }}">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                <span>Yeni Kayıt</span>
                            </a>
                        @endif
                    </nav>

                    <div class="mt-auto pt-6">
                        <div class="user-chip">
                            <div class="flex items-center gap-3">
                                <div class="flex h-11 w-11 items-center justify-center rounded-full bg-cyan-500/10 text-sm font-extrabold uppercase text-cyan-600">
                                    {{ strtoupper(substr(auth()->user()->username ?? auth()->user()->name ?? 'QR', 0, 2)) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="truncate text-[0.92rem] font-semibold text-brand-ink dark:text-white">{{ auth()->user()->name ?: auth()->user()->username }}</p>
                                    <p class="truncate text-[11px] text-slate-500 dark:text-slate-400">{{ auth()->user()->email ?: (auth()->user()->department?->name ?? 'Kurumsal kullanıcı') }}</p>
                                </div>
                            </div>
                        </div>

                        <form method="post" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn-logout mt-4 w-full">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                </svg>
                                <span>Oturumu Kapat</span>
                            </button>
                        </form>
                    </div>
                </div>
            </aside>

            <div data-sidebar-overlay class="fixed inset-0 z-40 hidden bg-black/20 backdrop-blur-sm lg:hidden"></div>
        @endauth

        <div class="flex min-w-0 flex-1 flex-col">
            @auth
                <div class="hidden px-6 pt-6 lg:flex lg:justify-end">
                    <button type="button" class="theme-toggle px-5 py-3" data-theme-toggle aria-label="Temayı değiştir">
                        <svg data-theme-icon="light" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v2m0 14v2m7-9h2M3 12H5m12.364 6.364 1.414 1.414M5.222 5.222l1.414 1.414m0 10.728-1.414 1.414m12.728-12.728-1.414 1.414M12 16a4 4 0 100-8 4 4 0 000 8z"></path>
                        </svg>
                        <svg data-theme-icon="dark" class="hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9 9 0 1012 21a8.96 8.96 0 008.354-5.646z"></path>
                        </svg>
                        <span data-theme-label>Tema</span>
                    </button>
                </div>

                <header class="px-4 pt-3 lg:hidden">
                    <div class="page-card apple-glass-heavy flex items-center justify-between rounded-[1.25rem] px-4 py-2.5 shadow-[0_12px_40px_rgba(16,32,42,0.08)]">
                        <button type="button" data-sidebar-toggle class="inline-flex h-11 w-11 items-center justify-center rounded-full border border-white/70 bg-white/70 text-brand-ink transition hover:bg-white dark:border-white/10 dark:bg-white/8 dark:text-white dark:hover:bg-white/12">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                            </svg>
                        </button>

                        <a href="{{ route('dashboard') }}" class="logo-shell px-4 py-2.5">
                            <img src="{{ asset('img/yee-logo.png') }}" alt="Yunus Emre Enstitüsü logosu" class="brand-logo h-7">
                        </a>

                        <button type="button" class="theme-toggle h-11 w-11 p-0" data-theme-toggle aria-label="Temayı değiştir">
                            <svg data-theme-icon="light" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v2m0 14v2m7-9h2M3 12H5m12.364 6.364 1.414 1.414M5.222 5.222l1.414 1.414m0 10.728-1.414 1.414m12.728-12.728-1.414 1.414M12 16a4 4 0 100-8 4 4 0 000 8z"></path>
                            </svg>
                            <svg data-theme-icon="dark" class="hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9 9 0 1012 21a8.96 8.96 0 008.354-5.646z"></path>
                            </svg>
                        </button>
                    </div>
                </header>
            @endauth

            <main class="flex-1 overflow-x-hidden {{ auth()->check() ? 'pt-3 pb-5 md:pb-6 lg:pt-4' : (request()->routeIs('landing') ? 'pb-0 pt-[4.9rem] md:pt-[6.8rem]' : 'pb-4 pt-[5rem] md:pb-6 md:pt-[5.75rem]') }}">
                @if (session('status'))
                    <div class="px-4 pb-4 md:px-6 lg:px-8">
                        <div class="mx-auto max-w-6xl">
                            <div class="status-banner">{{ session('status') }}</div>
                        </div>
                    </div>
                @endif

                @yield('content')
            </main>

            @guest
                @unless (request()->routeIs('landing'))
                    <footer class="px-4 pb-4 pt-2 md:px-6 md:pb-5 md:pt-3 lg:px-8">
                        <div class="mx-auto max-w-7xl">
                            <div class="page-card apple-glass rounded-[1.5rem] px-5 py-3 text-center">
                                <p class="text-sm font-semibold text-brand-ink dark:text-white">Yunus Emre Enstitüsü</p>
                                <p class="mt-1 text-[11px] font-bold uppercase tracking-[0.22em] text-slate-500 dark:text-slate-400">
                                    Dinamik QR Platformu - 2026
                                </p>
                            </div>
                        </div>
                    </footer>
                @endunless
            @endguest
        </div>
    </div>

    @stack('scripts')
</body>
</html>
