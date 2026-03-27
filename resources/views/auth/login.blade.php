<!DOCTYPE html>
<html lang="tr" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Kurumsal Giriş | Dinamik QR</title>
    <meta name="theme-color" content="#eef4f4" id="theme-color-meta">
    <link rel="icon" type="image/png" href="{{ asset('img/yee-favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('img/yee-favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
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
    <style>
        .login-background {
            position: fixed;
            inset: 0;
            z-index: -1;
            overflow: hidden;
            pointer-events: none;
        }

        .login-background::before,
        .login-background::after {
            content: '';
            position: absolute;
            border-radius: 999px;
            filter: blur(120px);
        }

        .login-background::before {
            left: -8rem;
            top: -6rem;
            height: 30rem;
            width: 30rem;
            background: rgba(106, 226, 231, 0.28);
        }

        .login-background::after {
            right: -6rem;
            bottom: -4rem;
            height: 24rem;
            width: 24rem;
            background: rgba(106, 226, 231, 0.18);
        }

        .login-shell {
            max-width: 64rem;
        }

        .login-viewport {
            min-height: 100svh;
        }

        .login-stage {
            display: grid;
            gap: 0.85rem;
            align-items: center;
        }

        .login-brand-panel,
        .login-form-panel {
            min-height: clamp(25.5rem, calc(100svh - 10rem), 30.5rem);
        }

        .login-brand-panel {
            padding: 1.9rem 1.75rem 1.55rem;
            background:
                radial-gradient(circle at top left, rgba(106, 226, 231, 0.18), transparent 34%),
                radial-gradient(circle at bottom right, rgba(18, 188, 200, 0.12), transparent 30%),
                linear-gradient(160deg, rgba(40, 74, 82, 0.96), rgba(60, 103, 111, 0.94) 54%, rgba(83, 137, 144, 0.9));
            opacity: 0.96;
        }

        .login-brand-panel::after {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at top right, rgba(255, 255, 255, 0.12), transparent 24%),
                radial-gradient(circle at bottom left, rgba(173, 244, 246, 0.1), transparent 30%),
                linear-gradient(180deg, rgba(255, 255, 255, 0.04), transparent 42%);
            pointer-events: none;
        }

        .login-brand-card {
            padding: 0.78rem 0.92rem;
            min-height: 4.15rem;
        }

        .login-form-panel {
            padding: 1.9rem 1.75rem 1.45rem;
            border: 1px solid rgba(255, 255, 255, 0.74);
            box-shadow:
                0 36px 88px rgba(68, 128, 139, 0.14),
                0 0 0 1px rgba(255, 255, 255, 0.34),
                inset 0 1px 0 rgba(255, 255, 255, 0.72);
        }

        .login-form-shell {
            display: flex;
            min-height: 100%;
            flex-direction: column;
        }

        .login-brand-content {
            display: flex;
            height: 100%;
            max-width: 31rem;
            margin-inline: auto;
            flex-direction: column;
            justify-content: center;
            gap: 1.45rem;
        }

        .login-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 0.72rem;
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.25em;
            text-transform: uppercase;
            color: #657b9d;
        }

        .login-eyebrow::before {
            content: '';
            width: 2rem;
            height: 1px;
            background: currentColor;
            opacity: 0.55;
        }

        .login-brand-top {
            margin-top: 0;
        }

        .login-logo {
            height: 3.8rem;
        }

        .login-input {
            height: 3.45rem;
            border-radius: 1.08rem;
            padding-inline: 1rem;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .login-primary {
            height: 3.45rem;
            font-size: 0.92rem;
            box-shadow: 0 20px 38px rgba(18, 188, 200, 0.22);
        }

        .login-brand-bottom {
            display: flex;
            flex: 0 0 auto;
            align-items: center;
            justify-content: center;
            padding-top: 0;
        }

        .login-brand-stack {
            display: flex;
            width: 100%;
            max-width: 26rem;
            flex-direction: column;
            gap: 0.8rem;
            margin-inline: auto;
            justify-content: center;
        }

        .login-brand-note {
            font-size: 0.88rem;
            font-weight: 600;
            color: rgba(242, 250, 251, 0.82);
        }

        .login-return-link {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            border-radius: 999px;
            border: 1px solid color-mix(in srgb, var(--surface-border-strong) 84%, transparent);
            background: color-mix(in srgb, var(--surface-soft) 92%, transparent);
            min-width: 4.35rem;
            padding: 0.58rem 0.68rem;
            overflow: hidden;
            color: var(--brand-ink);
            box-shadow: 0 10px 24px rgba(68, 128, 139, 0.06), inset 0 1px 0 rgba(255, 255, 255, 0.52);
            transition: transform 220ms ease, border-color 220ms ease, background 220ms ease, color 220ms ease, box-shadow 220ms ease;
        }

        .login-return-link::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(120deg, transparent 0%, rgba(18, 188, 200, 0.08) 50%, transparent 100%);
            opacity: 0;
            transform: translateX(-32%);
            transition: opacity 220ms ease, transform 320ms ease;
            pointer-events: none;
        }

        .login-return-link:hover {
            transform: translateY(-2px);
            border-color: rgba(18, 188, 200, 0.24);
            background: color-mix(in srgb, var(--muted-hover-bg) 94%, transparent);
            color: var(--accent-text);
            box-shadow: 0 18px 34px rgba(68, 128, 139, 0.12), inset 0 1px 0 rgba(255, 255, 255, 0.58);
        }

        .login-return-link:hover::before {
            opacity: 1;
            transform: translateX(0);
        }

        .login-return-link:focus-visible {
            outline: none;
            border-color: rgba(18, 188, 200, 0.34);
            box-shadow: 0 0 0 4px rgba(18, 188, 200, 0.12);
        }

        .login-return-link-icon {
            position: relative;
            z-index: 1;
            display: inline-flex;
            height: 1.95rem;
            width: 1.95rem;
            flex-shrink: 0;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            background: linear-gradient(135deg, rgba(18, 188, 200, 0.12), rgba(90, 218, 221, 0.08));
            color: var(--accent-text);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.42);
            transition: transform 220ms ease, background 220ms ease, box-shadow 220ms ease;
        }

        .login-return-link:hover .login-return-link-icon {
            transform: translateX(-2px) scale(1.03);
            background: linear-gradient(135deg, rgba(18, 188, 200, 0.18), rgba(90, 218, 221, 0.12));
            box-shadow: 0 10px 18px rgba(18, 188, 200, 0.18), inset 0 1px 0 rgba(255, 255, 255, 0.48);
        }

        .login-return-link-arrow {
            position: relative;
            z-index: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: currentColor;
            opacity: 0.68;
            transition: transform 220ms ease, opacity 220ms ease;
        }

        .login-return-link:hover .login-return-link-arrow {
            transform: translateX(-2px);
            opacity: 1;
        }

        html.dark .login-return-link {
            border-color: rgba(255, 255, 255, 0.08);
            background: rgba(255, 255, 255, 0.04);
            color: rgba(231, 243, 244, 0.84);
            box-shadow: 0 14px 30px rgba(0, 0, 0, 0.14), inset 0 1px 0 rgba(255, 255, 255, 0.04);
        }

        html.dark .login-return-link:hover {
            border-color: rgba(18, 188, 200, 0.26);
            background: rgba(255, 255, 255, 0.06);
            color: #8be8ec;
        }

        html.dark .login-return-link::before {
            background: linear-gradient(120deg, transparent 0%, rgba(139, 232, 236, 0.12) 50%, transparent 100%);
        }

        html.dark .login-return-link-icon {
            background: linear-gradient(135deg, rgba(18, 188, 200, 0.2), rgba(90, 218, 221, 0.08));
            color: #8be8ec;
        }

        @media (min-width: 64rem) {
            .login-stage {
                grid-template-columns: minmax(0, 0.76fr) minmax(0, 0.98fr);
                align-items: stretch;
            }

            .login-form-panel {
                transform: translateY(-4px);
            }
        }

        @media (max-width: 63.999rem) {
            .login-brand-panel,
            .login-form-panel {
                min-height: 0;
            }

            .login-brand-panel,
            .login-form-panel {
                padding: 1.55rem 1.2rem;
            }

            .login-viewport {
                min-height: auto;
            }

            .login-logo {
                height: 3.5rem;
            }

            .login-brand-bottom {
                padding-top: 1.1rem;
            }
        }

        @media (max-height: 860px) and (min-width: 64rem) {
            .login-brand-panel,
            .login-form-panel {
                min-height: clamp(24rem, calc(100svh - 7.2rem), 28.5rem);
            }

            .login-brand-panel {
                padding: 1.65rem 1.55rem 1.3rem;
            }

            .login-form-panel {
                padding: 1.65rem 1.55rem 1.3rem;
            }

            .login-brand-card {
                min-height: 3.95rem;
                padding: 0.72rem 0.82rem;
            }

            .login-input {
                height: 3.2rem;
            }

            .login-stage {
                gap: 0.8rem;
            }

            .login-form-panel {
                transform: translateY(-2px);
            }

            .login-compact-title {
                font-size: 2.15rem;
            }

            .login-compact-copy {
                margin-top: 0.65rem;
                font-size: 0.85rem;
                line-height: 1.5rem;
            }

            .login-form-title {
                font-size: 2.1rem;
            }

            .login-form-copy {
                margin-top: 0.65rem;
                font-size: 0.85rem;
                line-height: 1.5rem;
            }

            .login-form-actions {
                margin-top: 1.2rem;
            }

            .login-footer {
                margin-top: 1.2rem;
            }

            .login-brand-top {
                margin-top: 1.35rem;
            }

            .login-brand-bottom {
                padding-top: 1rem;
            }
        }

        @media (max-height: 760px) and (min-width: 64rem) {
            .login-shell {
                max-width: 61rem;
            }

            .login-brand-panel,
            .login-form-panel {
                min-height: clamp(22rem, calc(100svh - 5.5rem), 26rem);
            }

            .login-brand-panel {
                padding: 1.25rem 1.2rem 1rem;
            }

            .login-form-panel {
                padding: 1.3rem 1.2rem 1rem;
            }

            .login-brand-bottom {
                padding-top: 0.85rem;
            }

            .login-form-panel {
                transform: none;
            }

            .login-brand-card {
                min-height: 3.7rem;
                padding: 0.68rem 0.76rem;
            }
        }
    </style>
</head>
<body class="min-h-full sf-text">
    <div class="login-background"></div>

    <main class="login-viewport flex items-center justify-center px-4 py-4 md:px-5 md:py-5 lg:px-6 lg:py-6">
        <div class="login-shell w-full">
            <div class="mb-3 flex justify-end md:mb-4">
                <button type="button" class="theme-toggle px-4 py-2.5" data-theme-toggle aria-label="Temayi degistir">
                    <svg data-theme-icon="light" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v2m0 14v2m7-9h2M3 12H5m12.364 6.364 1.414 1.414M5.222 5.222l1.414 1.414m0 10.728-1.414 1.414m12.728-12.728-1.414 1.414M12 16a4 4 0 100-8 4 4 0 000 8z"></path>
                    </svg>
                    <svg data-theme-icon="dark" class="hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9 9 0 1012 21a8.96 8.96 0 008.354-5.646z"></path>
                    </svg>
                    <span data-theme-label>Koyu tema</span>
                </button>
            </div>

            <div class="login-stage">
                <section class="login-brand-panel brand-panel relative overflow-hidden rounded-[1.95rem]">
                    <div class="brand-panel-glow absolute inset-0"></div>

                    <div class="login-brand-content relative z-10">
                        <div class="logo-plinth w-fit px-2 py-1">
                            <img src="{{ asset('img/yee-logo.png') }}" alt="Yunus Emre Enstitusu logosu" class="brand-logo login-logo">
                        </div>

                        <div class="login-brand-top mt-6 max-w-[22rem]">
                            <span class="eyebrow text-cyan-100/80">Kurumsal erisim</span>
                            <h1 class="login-compact-title sf-display mt-3 text-[2rem] font-extrabold leading-[0.98] tracking-[-0.07em] text-white md:text-[2.55rem]">
                                Guvenli personel girisi
                            </h1>
                            <p class="login-compact-copy mt-3 max-w-[21rem] text-[0.87rem] leading-6 text-white/74">
                                Dinamik QR paneline yetkili hesabinizla erisin ve yonetimi tek ekrandan surdurun.
                            </p>
                        </div>

                        <div class="login-brand-bottom">
                            <div class="login-brand-stack">
                                <div class="login-brand-card brand-panel-card flex items-center gap-3 rounded-[1.3rem]">
                                    <div class="flex h-[2.65rem] w-[2.65rem] items-center justify-center rounded-full bg-white/8 text-cyan-100">
                                        <svg class="h-[1.05rem] w-[1.05rem]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3l7 4v5c0 5-3.5 8.5-7 9-3.5-.5-7-4-7-9V7l7-4z"></path>
                                        </svg>
                                    </div>
                                    <p class="login-brand-note">Yetkili kurumsal erisim</p>
                                </div>

                                <div class="login-brand-card brand-panel-card flex items-center gap-3 rounded-[1.3rem]">
                                    <div class="flex h-[2.65rem] w-[2.65rem] items-center justify-center rounded-full bg-white/8 text-cyan-100">
                                        <svg class="h-[1.05rem] w-[1.05rem]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7h16M4 12h10M4 17h7"></path>
                                        </svg>
                                    </div>
                                    <p class="login-brand-note">Baglanti ve QR yonetimi tek panelde</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="login-form-panel page-card glass-panel rounded-[1.95rem]">
                    <div class="login-form-shell">
                        <div class="max-w-[23rem]">
                            <span class="login-eyebrow">Personel girisi</span>
                            <h2 class="login-form-title sf-display mt-3 text-[1.95rem] font-extrabold tracking-[-0.07em] text-brand-ink dark:text-white md:text-[2.45rem]">
                                Kurumsal kimliginizle devam edin
                            </h2>
                            <p class="login-form-copy mt-3 max-w-[22rem] text-[0.88rem] leading-6 text-slate-600 dark:text-slate-300">
                                Erisim yalnizca yetkili kullanicilar icindir. Tum oturum hareketleri guvenlik kayitlarina islenir.
                            </p>
                        </div>

                        @if ($errors->any())
                            <div class="mt-5 rounded-[1.25rem] border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-600 dark:border-rose-400/20 dark:bg-rose-500/10 dark:text-rose-200">
                                {{ $errors->first() }}
                            </div>
                        @endif

                        <form action="{{ route('login.attempt') }}" method="post" class="login-form-actions mt-5 space-y-3.5">
                            @csrf

                            <div>
                                <label for="username" class="field-label">Kurumsal e-posta</label>
                                <input type="text" name="username" id="username" required autocomplete="username" autofocus value="{{ old('username') }}" class="input-shell login-input" placeholder="isim.soyisim@yee.org.tr">
                            </div>

                            <div>
                                <label for="password" class="field-label">Sifre</label>
                                <input type="password" name="password" id="password" required autocomplete="current-password" class="input-shell login-input" placeholder="Sifrenizi girin">
                            </div>

                            <button type="submit" class="primary-button login-primary w-full">Guvenli Giris Yap</button>
                        </form>

                        <div class="login-footer mt-auto flex flex-col gap-3 pt-5 text-sm text-slate-500 dark:text-slate-400 md:flex-row md:items-center md:justify-between">
                            <a href="{{ route('landing') }}" class="login-return-link" aria-label="Ana sayfaya don">
                                <span class="login-return-link-arrow" aria-hidden="true">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5l-7 7 7 7"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 12h16"></path>
                                    </svg>
                                </span>
                                <span class="login-return-link-icon" aria-hidden="true">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10.75L12 4l9 6.75"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.25 10v8.25A1.75 1.75 0 007 20h10a1.75 1.75 0 001.75-1.75V10"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 20v-5.25h4.5V20"></path>
                                    </svg>
                                </span>
                            </a>
                            <span class="text-[0.64rem] font-medium uppercase tracking-[0.2em] text-slate-400 dark:text-slate-500">Tum erisimler kayit altindadir</span>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </main>
</body>
</html>
