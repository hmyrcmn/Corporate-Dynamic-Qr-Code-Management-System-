@extends('layouts.app')

@section('title', 'Dinamik QR Yönetim Platformu')

@push('styles')
    <style>
        .landing-shell {
            width: min(100%, 65rem);
            max-width: none;
        }

        .landing-viewport {
            min-height: calc(100svh - 5.3rem);
        }

        .landing-stage {
            min-height: clamp(34rem, calc(100svh - 7.4rem), 39rem);
            height: 100%;
        }

        .landing-grid {
            display: grid;
            gap: 1.35rem;
            min-height: 100%;
            align-items: center;
        }

        .landing-copy {
            max-width: 27rem;
            min-height: 0;
            padding-block: 0.1rem;
        }

        .landing-copy-main {
            display: flex;
            flex-direction: column;
            gap: 0.9rem;
        }

        .landing-copy-footer {
            margin-top: auto;
            padding-top: 1rem;
        }

        .landing-eyebrow-pill {
            padding: 0.64rem 1rem;
            font-size: 0.68rem;
            font-weight: 800;
            letter-spacing: 0.22em;
            text-transform: uppercase;
            border: 0;
            background: transparent;
            box-shadow: none;
            color: rgba(16, 32, 42, 0.46);
        }

        .landing-title {
            max-width: 8.9ch;
            font-size: clamp(2.05rem, 3.25vw, 3rem);
            line-height: 1.02;
            letter-spacing: -0.06em;
            text-wrap: balance;
        }

        .landing-copy-text {
            max-width: 25rem;
            font-size: 0.88rem;
            line-height: 1.58;
        }

        .landing-feature {
            display: flex;
            align-items: center;
            gap: 0.7rem;
            padding-top: 0.9rem;
            border-top: 1px solid rgba(16, 32, 42, 0.06);
        }

        .landing-feature-icon {
            height: 2.6rem;
            width: 2.6rem;
            border-radius: 0.85rem;
            box-shadow: none;
        }

        .landing-feature-label {
            font-size: 0.68rem;
            font-weight: 800;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: rgba(16, 32, 42, 0.46);
        }

        .landing-feature-copy {
            margin-top: 0.2rem;
            font-size: 0.84rem;
            line-height: 1.45;
            color: rgba(16, 32, 42, 0.72);
        }

        html.dark .landing-feature {
            border-top-color: rgba(255, 255, 255, 0.08);
        }

        html.dark .landing-feature-label {
            color: rgba(231, 243, 244, 0.5);
        }

        html.dark .landing-eyebrow-pill {
            color: rgba(231, 243, 244, 0.56);
        }

        html.dark .landing-feature-copy {
            color: rgba(231, 243, 244, 0.8);
        }

        .landing-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.65rem;
        }

        .landing-actions .brand-button,
        .landing-actions .ghost-button {
            min-height: 3.2rem;
            padding-inline: 1.35rem;
            font-size: 0.9rem;
            font-weight: 700;
        }

        .landing-chip-row {
            display: flex;
            flex-wrap: wrap;
            gap: 0.55rem;
            align-items: center;
        }

        .landing-chip {
            padding: 0.52rem 0.75rem;
            font-size: 0.76rem;
            font-weight: 600;
        }

        .landing-guide {
            display: flex;
            align-items: center;
            justify-content: flex-end;
        }

        .landing-flow {
            width: min(100%, 23.5rem);
            min-height: 0;
            margin-left: auto;
            padding: 1rem;
            height: auto;
        }

        .landing-flow-steps {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
            margin-top: 0.85rem;
        }

        .landing-step-card {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.78rem 0;
        }

        .landing-step-icon {
            display: inline-flex;
            height: 2.45rem;
            width: 2.45rem;
            flex-shrink: 0;
            align-items: center;
            justify-content: center;
            border-radius: 0.8rem;
            background: linear-gradient(135deg, rgba(18, 188, 200, 0.14), rgba(90, 218, 221, 0.08));
            color: var(--accent-text);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.5);
        }

        .landing-step-icon svg {
            height: 0.9rem;
            width: 0.9rem;
        }

        .landing-step-copy {
            min-width: 0;
        }

        .landing-step-title {
            font-size: 0.82rem;
            font-weight: 700;
            color: rgba(16, 32, 42, 0.62);
        }

        .landing-step-text {
            margin-top: 0.18rem;
            font-size: 0.78rem;
            line-height: 1.45;
            color: rgba(16, 32, 42, 0.52);
        }

        .landing-flow.is-guided {
            border-color: rgba(18, 188, 200, 0.34);
            box-shadow: 0 26px 80px rgba(18, 188, 200, 0.2), 0 0 0 1px rgba(18, 188, 200, 0.2);
        }

        html.dark .landing-step-title {
            color: rgba(231, 243, 244, 0.78);
        }

        html.dark .landing-step-text {
            color: rgba(231, 243, 244, 0.58);
        }

        @media (min-width: 64rem) {
            .landing-grid {
                grid-template-columns: minmax(0, 1fr) minmax(18.75rem, 0.78fr);
                align-items: center;
            }
        }

        @media (min-width: 48rem) {
            .landing-viewport {
                min-height: calc(100svh - 6.4rem);
            }
        }

        @media (max-width: 63.999rem) {
            .landing-shell {
                width: min(100%, 42rem);
            }

            .landing-viewport {
                height: auto;
                min-height: auto;
            }

            .landing-stage {
                min-height: 0;
                height: auto;
            }

            .landing-copy {
                max-width: none;
                text-align: center;
            }

            .landing-feature {
                justify-content: center;
            }

            .landing-chip-row,
            .landing-actions {
                justify-content: center;
            }

            .landing-copy-footer {
                margin-top: 1.2rem;
            }

            .landing-title,
            .landing-copy-text {
                max-width: none;
            }

            .landing-guide {
                justify-content: center;
            }

            .landing-flow {
                width: 100%;
            }
        }

        @media (max-height: 860px) and (min-width: 64rem) {
            .landing-shell {
                width: min(100%, 62rem);
            }

            .landing-stage {
                min-height: clamp(31rem, calc(100svh - 6.2rem), 35.5rem);
            }

            .landing-title {
                font-size: clamp(1.9rem, 2.85vw, 2.6rem);
            }

            .landing-feature-icon {
                height: 2.35rem;
                width: 2.35rem;
            }

            .landing-flow {
                width: min(100%, 21.5rem);
                padding: 0.85rem;
            }

            .landing-chip-row {
                margin-top: 0.7rem;
            }
        }

        @media (max-height: 760px) and (min-width: 64rem) {
            .landing-shell {
                width: min(100%, 59rem);
            }

            .landing-stage {
                min-height: clamp(28rem, calc(100svh - 5.5rem), 32rem);
            }

            .landing-feature {
                margin-top: 1rem;
            }

            .landing-actions {
                margin-top: 1rem;
            }

            .landing-chip-row {
                display: flex;
                margin-top: 0.75rem;
            }

            .landing-flow-steps {
                margin-top: 0.6rem;
            }
        }
    </style>
@endpush

@section('content')
    <section class="landing-shell landing-viewport page-shell flex items-center py-0">
        <div class="w-full">
            <div class="landing-stage page-card apple-glass-heavy relative overflow-hidden rounded-[1.95rem] px-4 py-4 md:px-5 md:py-5 lg:px-6 lg:py-6">
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_left_center,rgba(106,226,231,0.14),transparent_34%),radial-gradient(circle_at_right_center,rgba(106,226,231,0.12),transparent_30%),linear-gradient(90deg,rgba(255,255,255,0.28),transparent_18%,transparent_82%,rgba(255,255,255,0.28))]"></div>

                <div class="landing-grid relative z-10 h-full">
                    <div class="landing-copy flex flex-col">
                        <div class="landing-copy-main">
                            <div class="surface-chip landing-eyebrow-pill inline-flex w-fit items-center rounded-full">
                                Kurumsal Dinamik QR Paneli
                            </div>

                            <h1 class="landing-title sf-display font-extrabold text-brand-ink dark:text-white">
                                QR s&uuml;recini h&#305;zl&#305;ca ba&#351;lat&#305;n.
                            </h1>

                            <p class="landing-copy-text text-slate-600 dark:text-slate-300">
                                Giri&#351; yap&#305;n, ba&#287;lant&#305;n&#305;z&#305; olu&#351;turun ve QR dosyan&#305;z&#305; al&#305;n.
                            </p>

                            <div class="landing-feature mt-4">
                                <div class="landing-feature-icon icon-shell flex items-center justify-center">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.9" d="M12 3l7 4v5c0 5-3.5 8.5-7 9-3.5-.5-7-4-7-9V7l7-4z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.9" d="M9.75 12.25a2.25 2.25 0 114.5 0v.75h.25a.75.75 0 01.75.75v2a.75.75 0 01-.75.75h-5a.75.75 0 01-.75-.75v-2a.75.75 0 01.75-.75h.25v-.75z"></path>
                                    </svg>
                                </div>

                                <div class="min-w-0">
                                    <p class="landing-feature-label">Kurumsal eri&#351;im</p>
                                    <p class="landing-feature-copy">Yetkili hesapla g&uuml;venli giri&#351;.</p>
                                </div>
                            </div>
                        </div>

                        <div class="landing-copy-footer">
                            <div class="landing-actions">
                                <a href="{{ route('login') }}" class="brand-button">
                                    <span>Kurumsal Giri&#351;</span>
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                                    </svg>
                                </a>

                                <button type="button" class="ghost-button" data-flow-trigger aria-controls="quick-flow">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span>Ak&#305;&#351;&#305; G&ouml;r</span>
                                </button>
                            </div>

                            <div class="landing-chip-row mt-3">
                                <div class="surface-chip landing-chip inline-flex items-center gap-2 rounded-full">
                                    <svg class="h-4 w-4 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span>Kurumsal eri&#351;im</span>
                                </div>
                                <div class="surface-chip landing-chip inline-flex items-center gap-2 rounded-full">
                                    <svg class="h-4 w-4 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span>Kolay kullan&#305;m</span>
                                </div>
                                <div class="surface-chip landing-chip inline-flex items-center gap-2 rounded-full">
                                    <svg class="h-4 w-4 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7h16M4 12h10M4 17h7"></path>
                                    </svg>
                                    <span>Tek panel</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="quick-flow" class="landing-guide relative">
                        <div class="landing-flow page-card apple-glass relative overflow-hidden rounded-[1.5rem]" data-flow-panel tabindex="-1">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <h2 class="text-[1.05rem] font-bold tracking-[-0.035em] text-brand-ink dark:text-white">Nas&#305;l ilerlenir?</h2>
                                    <p class="mt-1 text-[0.72rem] uppercase tracking-[0.18em] text-slate-400 dark:text-slate-500">H&#305;zl&#305; rehber</p>
                                </div>
                                <div class="status-pill px-3 py-1.5">3 ad&#305;m</div>
                            </div>

                            <div class="landing-flow-steps">
                                <div class="landing-step-card">
                                    <div class="landing-step-icon">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3l7 4v5c0 5-3.5 8.5-7 9-3.5-.5-7-4-7-9V7l7-4z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 12.5l1.5 1.5L14.5 11"></path>
                                        </svg>
                                    </div>
                                    <div class="landing-step-copy">
                                        <p class="landing-step-title">Giri&#351; yap&#305;n</p>
                                        <p class="landing-step-text">Kurumsal hesab&#305;n&#305;zla giri&#351; yap&#305;n.</p>
                                    </div>
                                </div>

                                <div class="landing-step-card">
                                    <div class="landing-step-icon">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                        </svg>
                                    </div>
                                    <div class="landing-step-copy">
                                        <p class="landing-step-title">Kay&#305;t olu&#351;turun</p>
                                        <p class="landing-step-text">Ba&#351;l&#305;k ve URL ekleyin.</p>
                                    </div>
                                </div>

                                <div class="landing-step-card">
                                    <div class="landing-step-icon">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v12m0 0l4-4m-4 4l-4-4M5 19h14"></path>
                                        </svg>
                                    </div>
                                    <div class="landing-step-copy">
                                        <p class="landing-step-title">QR dosyas&#305;n&#305; al&#305;n</p>
                                        <p class="landing-step-text">Haz&#305;r g&ouml;rseli indirin.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const trigger = document.querySelector('[data-flow-trigger]');
            const panel = document.querySelector('[data-flow-panel]');

            if (!trigger || !panel) {
                return;
            }

            let clearTimer;

            trigger.addEventListener('click', () => {
                panel.scrollIntoView({ behavior: 'smooth', block: 'center' });
                panel.classList.remove('is-guided');
                window.clearTimeout(clearTimer);

                window.requestAnimationFrame(() => {
                    panel.classList.add('is-guided');
                    panel.focus({ preventScroll: true });

                    clearTimer = window.setTimeout(() => {
                        panel.classList.remove('is-guided');
                    }, 2600);
                });
            });
        });
    </script>
@endpush
