@extends('layouts.app')

@section('title', 'Birim Merkezi | Dinamik QR')

@push('styles')
    <style>
        .department-hub-shell {
            width: min(100%, 88rem);
            max-width: none;
        }

        .department-hub-grid {
            display: grid;
            gap: 1.35rem;
            grid-template-columns: 1fr;
        }

        @media (min-width: 56rem) {
            .department-hub-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        .department-hub-card {
            position: relative;
            overflow: hidden;
            display: flex;
            min-height: 19.5rem;
            flex-direction: column;
            gap: 0.95rem;
            border-radius: 2rem;
            padding: 1.3rem;
            text-decoration: none;
            color: inherit;
            background:
                radial-gradient(circle at top right, rgba(123, 228, 232, 0.18), transparent 34%),
                linear-gradient(145deg, rgba(255, 255, 255, 0.78), rgba(255, 255, 255, 0.5));
            border: 1px solid rgba(255, 255, 255, 0.78);
            box-shadow: 0 24px 58px rgba(16, 32, 42, 0.09), inset 0 1px 0 rgba(255, 255, 255, 0.88);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            transition: transform 260ms ease, box-shadow 260ms ease, border-color 260ms ease;
        }

        .department-hub-card:hover {
            transform: translateY(-4px);
            border-color: rgba(18, 188, 200, 0.26);
            box-shadow: 0 28px 62px rgba(18, 188, 200, 0.14), inset 0 1px 0 rgba(255, 255, 255, 0.9);
        }

        html.dark .department-hub-card {
            background:
                radial-gradient(circle at top right, rgba(123, 228, 232, 0.12), transparent 34%),
                linear-gradient(145deg, rgba(22, 33, 39, 0.72), rgba(12, 18, 23, 0.52));
            border-color: rgba(255, 255, 255, 0.08);
            box-shadow: 0 24px 58px rgba(0, 0, 0, 0.36), inset 0 1px 0 rgba(255, 255, 255, 0.05);
        }

        html.dark .department-hub-card:hover {
            border-color: rgba(18, 188, 200, 0.24);
            box-shadow: 0 30px 64px rgba(0, 0, 0, 0.44), inset 0 1px 0 rgba(255, 255, 255, 0.08);
        }

        .department-hub-badge {
            display: inline-flex;
            align-items: center;
            width: fit-content;
            border-radius: 999px;
            padding: 0.55rem 0.9rem;
            background: rgba(18, 188, 200, 0.1);
            color: #0f8d97;
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.18em;
            text-transform: uppercase;
        }

        html.dark .department-hub-badge {
            background: rgba(18, 188, 200, 0.18);
            color: #8be8ec;
        }

        .department-hub-card-header {
            display: grid;
            gap: 0.65rem;
        }

        .department-hub-card-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 0.85rem;
        }

        .department-hub-card-title {
            font-size: 1.3rem;
            font-weight: 800;
            letter-spacing: -0.04em;
            line-height: 1.18;
            color: var(--brand-ink);
        }

        html.dark .department-hub-card-title {
            color: white;
        }

        .department-hub-card-copy {
            max-width: 34rem;
            font-size: 0.84rem;
            line-height: 1.6;
            color: rgba(16, 32, 42, 0.62);
        }

        html.dark .department-hub-card-copy {
            color: rgba(231, 243, 244, 0.68);
        }

        .department-hub-card-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .department-hub-meta-pill {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: 0.36rem 0.7rem;
            background: rgba(255, 255, 255, 0.68);
            border: 1px solid rgba(255, 255, 255, 0.82);
            font-size: 0.68rem;
            font-weight: 800;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: rgba(16, 32, 42, 0.56);
        }

        html.dark .department-hub-meta-pill {
            background: rgba(255, 255, 255, 0.05);
            border-color: rgba(255, 255, 255, 0.08);
            color: rgba(231, 243, 244, 0.62);
        }

        .department-hub-summary {
            position: relative;
            overflow: hidden;
            align-self: flex-start;
            min-width: min(100%, 15rem);
            border-radius: 1.65rem;
            padding: 1rem 1.15rem 1.05rem;
            background:
                radial-gradient(circle at top right, rgba(123, 228, 232, 0.22), transparent 42%),
                linear-gradient(145deg, rgba(255, 255, 255, 0.82), rgba(255, 255, 255, 0.58));
            border: 1px solid rgba(255, 255, 255, 0.82);
            box-shadow: 0 20px 46px rgba(16, 32, 42, 0.08), inset 0 1px 0 rgba(255, 255, 255, 0.84);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
        }

        .department-hub-summary::after {
            content: '';
            position: absolute;
            inset: auto -2.4rem -2.9rem auto;
            height: 7.2rem;
            width: 7.2rem;
            border-radius: 999px;
            background: radial-gradient(circle, rgba(18, 188, 200, 0.18), rgba(18, 188, 200, 0));
            pointer-events: none;
        }

        html.dark .department-hub-summary {
            background:
                radial-gradient(circle at top right, rgba(123, 228, 232, 0.14), transparent 42%),
                linear-gradient(145deg, rgba(24, 35, 41, 0.74), rgba(12, 18, 23, 0.54));
            border-color: rgba(255, 255, 255, 0.08);
            box-shadow: 0 22px 50px rgba(0, 0, 0, 0.36), inset 0 1px 0 rgba(255, 255, 255, 0.04);
        }

        .department-hub-summary-label {
            display: block;
            position: relative;
            z-index: 1;
            font-size: 0.68rem;
            font-weight: 800;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: rgba(16, 32, 42, 0.48);
        }

        html.dark .department-hub-summary-label {
            color: rgba(231, 243, 244, 0.5);
        }

        .department-hub-summary-value {
            position: relative;
            z-index: 1;
            margin-top: 0.45rem;
            color: var(--brand-ink);
        }

        html.dark .department-hub-summary-value {
            color: white;
        }

        .department-hub-summary-count {
            font-size: 2.15rem;
            font-weight: 800;
            letter-spacing: -0.05em;
            line-height: 1;
        }

        .department-hub-metrics {
            display: grid;
            gap: 0.65rem;
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .department-hub-metric {
            border-radius: 1.15rem;
            padding: 0.8rem 0.85rem;
            background: rgba(255, 255, 255, 0.68);
            border: 1px solid rgba(255, 255, 255, 0.82);
            display: grid;
            gap: 0.18rem;
        }

        html.dark .department-hub-metric {
            background: rgba(255, 255, 255, 0.04);
            border-color: rgba(255, 255, 255, 0.08);
        }

        .department-hub-metric-value {
            font-size: 1.18rem;
            font-weight: 800;
            color: var(--brand-ink);
            letter-spacing: -0.04em;
            line-height: 1.15;
        }

        html.dark .department-hub-metric-value {
            color: white;
        }

        .department-hub-metric-label {
            font-size: 0.66rem;
            font-weight: 800;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: rgba(16, 32, 42, 0.5);
            line-height: 1.35;
        }

        html.dark .department-hub-metric-label {
            color: rgba(231, 243, 244, 0.5);
        }

        .department-hub-status {
            display: inline-flex;
            flex-shrink: 0;
            align-items: center;
            border-radius: 999px;
            padding: 0.34rem 0.62rem;
            font-size: 0.64rem;
            font-weight: 800;
            letter-spacing: 0.14em;
            text-transform: uppercase;
        }

        .department-hub-status.is-active {
            background: rgba(17, 163, 73, 0.12);
            color: #0f8d42;
        }

        .department-hub-status.is-passive {
            background: rgba(148, 163, 184, 0.14);
            color: #516173;
        }

        .department-hub-open {
            margin-top: auto;
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding-top: 0.2rem;
            font-size: 0.8rem;
            font-weight: 800;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: #0f8d97;
        }

        html.dark .department-hub-open {
            color: #8be8ec;
        }

        @media (max-width: 63.99rem) {
            .department-hub-summary {
                width: 100%;
                min-width: 0;
                align-self: stretch;
            }
        }

        @media (max-width: 47.99rem) {
            .department-hub-card {
                min-height: auto;
                gap: 0.8rem;
                border-radius: 1.5rem;
                padding: 1rem;
            }

            .department-hub-card:hover {
                transform: none;
            }

            .department-hub-card-top {
                flex-direction: column;
                align-items: flex-start;
            }

            .department-hub-card-title {
                font-size: 1.08rem;
            }

            .department-hub-card-copy {
                font-size: 0.8rem;
                line-height: 1.5;
            }

            .department-hub-metrics {
                grid-template-columns: 1fr;
            }

            .department-hub-metric {
                padding: 0.72rem 0.8rem;
            }

            .department-hub-open {
                font-size: 0.74rem;
                letter-spacing: 0.14em;
            }
        }
    </style>
@endpush

@section('content')
    <section class="department-hub-shell page-shell flex flex-col gap-5 pb-4">
        <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
            <div class="max-w-3xl">
                <span class="eyebrow">Kontrol paneli</span>
                <h2
                    class="sf-display mt-2 text-[1.9rem] font-extrabold tracking-[-0.06em] text-brand-ink dark:text-white md:text-[2.35rem]">
                    Birim Merkezi
                </h2>
                <p class="mt-2 text-[0.88rem] leading-7 text-slate-600 dark:text-slate-300">
                    Tüm birimler burada listelenir. Ayrıntılı QR işlemleri için karta tıklayarak ilgili birimin sayfasına
                    geçebilirsiniz.
                </p>
            </div>

            <div class="department-hub-summary" aria-label="Toplam birim özeti">
                <span class="department-hub-summary-label">Toplam Birim</span>
                <div class="department-hub-summary-value">
                    <span class="department-hub-summary-count">{{ $departments->count() }}</span>
                </div>
            </div>
        </div>

        <div class="department-hub-grid">
            @forelse ($departments as $department)
                <a href="{{ route('dashboard.department', $department) }}" class="department-hub-card">
                    <span class="department-hub-badge">Birim Kartı</span>

                    <div class="department-hub-card-header">
                        <div class="department-hub-card-top">
                            <h3 class="department-hub-card-title">{{ $department->name }}</h3>
                            <span class="department-hub-status {{ $department->is_active ? 'is-active' : 'is-passive' }}">
                                {{ $department->is_active ? 'Aktif' : 'Pasif' }}
                            </span>
                        </div>
                        <p class="department-hub-card-copy">
                            QR yönetimi, aktif kayıtlar ve tarama özeti bu kartta yer alır.
                        </p>
                        <div class="department-hub-card-meta">
                            <span class="department-hub-meta-pill">{{ $department->qr_codes_count }} QR Kayıt</span>
                            <span class="department-hub-meta-pill">{{ $department->scans_count }} Tarama</span>
                        </div>
                    </div>

                    <div class="department-hub-metrics">
                        <div class="department-hub-metric">
                            <div class="department-hub-metric-value">{{ $department->qr_codes_count }}</div>
                            <div class="department-hub-metric-label">Toplam QR</div>
                        </div>
                        <div class="department-hub-metric">
                            <div class="department-hub-metric-value">{{ $department->active_qr_codes_count }}</div>
                            <div class="department-hub-metric-label">Aktif QR</div>
                        </div>
                        <div class="department-hub-metric">
                            <div class="department-hub-metric-value">{{ $department->scans_count }}</div>
                            <div class="department-hub-metric-label">Tarama</div>
                        </div>
                    </div>

                    <div class="department-hub-open">
                        <span>Birim Sayfasını Aç</span>
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </div>
                </a>
            @empty
                <div class="page-card apple-glass-heavy col-span-full rounded-[2rem] px-6 py-10 text-center">
                    <p class="text-lg font-semibold text-brand-ink dark:text-white">Tanımlı birim bulunmuyor</p>
                    <p class="mt-2 text-[0.9rem] leading-7 text-slate-600 dark:text-slate-300">
                        Birim kayıtları oluşturulduğunda bu ekranda iki sütunlu kart düzeniyle görünecek.
                    </p>
                </div>
            @endforelse
        </div>
    </section>
@endsection
