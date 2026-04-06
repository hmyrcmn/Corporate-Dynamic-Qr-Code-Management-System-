@extends('layouts.app')

@section('title', 'Yönetim Paneli | Dinamik QR')

@push('styles')
    <style>
        .dashboard-shell {
            width: min(100%, 82rem);
            max-width: none;
            min-height: calc(100svh - 6.2rem);
        }

        .dashboard-top {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            margin-bottom: 1rem;
        }

        @media (min-width: 48rem) {
            .dashboard-top {
                flex-direction: row;
                align-items: flex-end;
                justify-content: space-between;
            }
        }

        .dashboard-title-area {
            max-width: 38rem;
        }

        .dashboard-department {
            color: var(--accent-text);
        }

        .dashboard-action-rail {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.8rem;
        }

        /* Glass Cards */
        .apple-glass-panel {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.55), rgba(255, 255, 255, 0.35));
            backdrop-filter: blur(28px);
            -webkit-backdrop-filter: blur(28px);
            border: 1px solid rgba(255, 255, 255, 0.65);
            border-radius: 1.8rem;
            box-shadow: 0 24px 54px rgba(31, 47, 50, 0.08), inset 0 1px 0 rgba(255, 255, 255, 0.8);
        }

        html.dark .apple-glass-panel {
            background: linear-gradient(135deg, rgba(20, 30, 35, 0.65), rgba(10, 15, 20, 0.45));
            border-color: rgba(255, 255, 255, 0.08);
            box-shadow: 0 24px 54px rgba(0, 0, 0, 0.35), inset 0 1px 0 rgba(255, 255, 255, 0.05);
        }

        /* Metrics */
        .dashboard-metrics {
            display: grid;
            gap: 1.25rem;
            grid-template-columns: repeat(auto-fit, minmax(18rem, 1fr));
            margin-bottom: 1.75rem;
            perspective: 1400px;
        }

        .dashboard-metric {
            position: relative;
            overflow: hidden;
            border-radius: 1.6rem;
            padding: 1.45rem 1.6rem;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.75), rgba(255, 255, 255, 0.55));
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.7);
            box-shadow: 0 16px 36px rgba(18, 188, 200, 0.07), inset 0 1px 0 rgba(255, 255, 255, 0.6);
            transition: all 400ms cubic-bezier(0.2, 0.8, 0.2, 1);
            display: flex;
            flex-direction: column;
            gap: 1rem;
            text-decoration: none;
            color: inherit;
        }

        html.dark .dashboard-metric {
            background: linear-gradient(180deg, rgba(30, 42, 48, 0.55), rgba(15, 22, 26, 0.35));
            border-color: rgba(255, 255, 255, 0.06);
            box-shadow: 0 16px 36px rgba(0, 0, 0, 0.25), inset 0 1px 0 rgba(255, 255, 255, 0.03);
        }

        .dashboard-metric:hover {
            transform: translateY(-6px) scale(1.02) rotateX(4deg) rotateY(-2deg);
            border-color: rgba(18, 188, 200, 0.45);
            box-shadow: -8px 24px 64px rgba(18, 188, 200, 0.18), inset 0 2px 0 rgba(255, 255, 255, 0.9);
            z-index: 10;
        }

        html.dark .dashboard-metric:hover {
            border-color: rgba(18, 188, 200, 0.25);
            box-shadow: 0 24px 54px rgba(0, 0, 0, 0.45), inset 0 1px 0 rgba(255, 255, 255, 0.08);
        }

        .dashboard-metric.is-active {
            border-color: rgba(18, 188, 200, 0.45);
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.95), rgba(240, 252, 253, 0.8));
            box-shadow: 0 0 0 1px rgba(18, 188, 200, 0.25), 0 20px 48px rgba(18, 188, 200, 0.12);
        }

        html.dark .dashboard-metric.is-active {
            border-color: rgba(18, 188, 200, 0.45);
            background: linear-gradient(180deg, rgba(18, 188, 200, 0.18), rgba(18, 188, 200, 0.08));
            box-shadow: 0 0 0 1px rgba(18, 188, 200, 0.35), 0 20px 48px rgba(0, 0, 0, 0.4);
        }

        .dashboard-metric-icon {
            display: inline-flex;
            height: 3.2rem;
            width: 3.2rem;
            align-items: center;
            justify-content: center;
            border-radius: 1rem;
            background: linear-gradient(135deg, rgba(18, 188, 200, 0.15), rgba(90, 218, 221, 0.05));
            color: #0f8d97;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.5);
            transition: transform 300ms ease;
        }

        html.dark .dashboard-metric-icon {
            background: linear-gradient(135deg, rgba(18, 188, 200, 0.25), rgba(90, 218, 221, 0.05));
            color: #8be8ec;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.1);
        }

        .dashboard-metric:hover .dashboard-metric-icon {
            transform: scale(1.08) rotate(-4deg);
        }

        .dashboard-metric-value {
            font-size: 2.2rem;
            font-weight: 800;
            letter-spacing: -0.04em;
            color: var(--brand-ink);
            line-height: 1.1;
            margin-top: 0.25rem;
        }

        html.dark .dashboard-metric-value {
            color: white;
        }

        .dashboard-metric-copy {
            font-size: 0.84rem;
            line-height: 1.6;
            color: rgba(16, 32, 42, 0.6);
            margin-top: 0.25rem;
        }

        html.dark .dashboard-metric-copy {
            color: rgba(231, 243, 244, 0.6);
        }

        .dashboard-metric-foot {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: auto;
            border-top: 1px solid rgba(16, 32, 42, 0.06);
            padding-top: 1rem;
        }

        html.dark .dashboard-metric-foot {
            border-top-color: rgba(255, 255, 255, 0.06);
        }

        .dashboard-metric-state {
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            color: var(--eyebrow-color);
        }

        .dashboard-metric-arrow {
            display: inline-flex;
            height: 2.15rem;
            width: 2.15rem;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            background: rgba(16, 32, 42, 0.04);
            color: var(--brand-ink);
            transition: all 300ms cubic-bezier(0.2, 0.8, 0.2, 1);
        }

        html.dark .dashboard-metric-arrow {
            background: rgba(255, 255, 255, 0.08);
            color: white;
        }

        .dashboard-metric:hover .dashboard-metric-arrow {
            background: rgba(18, 188, 200, 0.15);
            color: #0f8d97;
            transform: translateX(6px);
        }

        html.dark .dashboard-metric:hover .dashboard-metric-arrow {
            background: rgba(18, 188, 200, 0.25);
            color: #8be8ec;
        }

        /* List Card */
        .dashboard-list-card {
            display: flex;
            flex-direction: column;
            padding: 1.5rem;
            flex: 1;
            overflow: hidden;
        }

        .dashboard-list-hero {
            padding-bottom: 1.25rem;
            border-bottom: 1px solid rgba(16, 32, 42, 0.06);
            margin-bottom: 0.75rem;
        }

        html.dark .dashboard-list-hero {
            border-bottom-color: rgba(255, 255, 255, 0.06);
        }

        .dashboard-head-copy {
            margin-top: 0.5rem;
            font-size: 0.88rem;
            color: rgba(16, 32, 42, 0.6);
            max-width: 40rem;
            line-height: 1.6;
        }

        html.dark .dashboard-head-copy {
            color: rgba(231, 243, 244, 0.6);
        }

        /* Table */
        .dashboard-table-head {
            display: none;
            padding: 0 1.25rem 0.85rem;
            font-size: 0.78rem;
            font-weight: 800;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: rgba(16, 32, 42, 0.45);
        }

        html.dark .dashboard-table-head {
            color: rgba(231, 243, 244, 0.45);
        }

        .dashboard-table-head span {
            display: block;
        }

        .dashboard-list-scroll {
            min-height: 0;
            flex: 1 1 auto;
            overflow: auto;
            padding-right: 0.5rem;
            perspective: 1200px;
        }

        .dashboard-row {
            padding: 1.15rem 1.25rem;
            border-radius: 1.25rem;
            transition: all 300ms cubic-bezier(0.2, 0.8, 0.2, 1);
            background: transparent;
            align-items: center;
            border: 1px solid transparent;
            margin-bottom: 0.45rem;
            position: relative;
        }

        .dashboard-row:hover {
            background: rgba(255, 255, 255, 0.6);
            border-color: rgba(255, 255, 255, 0.9);
            box-shadow: 0 14px 42px rgba(18, 188, 200, 0.12);
            transform: translateY(-2px) scale(1.01) translateZ(10px);
            z-index: 2;
        }

        html.dark .dashboard-row:hover {
            background: rgba(255, 255, 255, 0.04);
            border-color: rgba(255, 255, 255, 0.08);
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.25);
        }

        .dashboard-row-title {
            font-size: 1.1rem;
            font-weight: 800;
            color: var(--brand-ink);
            letter-spacing: -0.01em;
        }

        html.dark .dashboard-row-title {
            color: white;
        }

        .dashboard-row-subtitle {
            margin-top: 0.45rem;
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.55rem;
            font-size: 0.74rem;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: rgba(16, 32, 42, 0.5);
        }

        html.dark .dashboard-row-subtitle {
            color: rgba(231, 243, 244, 0.5);
        }

        .dashboard-link-code {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 2.3rem;
            border-radius: 999px;
            border: 1px solid rgba(18, 188, 200, 0.2);
            background: rgba(18, 188, 200, 0.08);
            padding: 0.4rem 1rem;
            font-size: 0.84rem;
            font-weight: 800;
            color: #0c757d;
            letter-spacing: 0.05em;
        }

        html.dark .dashboard-link-code {
            background: rgba(18, 188, 200, 0.15);
            color: #8be8ec;
            border-color: rgba(18, 188, 200, 0.3);
        }

        .dashboard-scan-pill {
            display: inline-flex;
            min-width: 4.8rem;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            border: 1px solid rgba(16, 32, 42, 0.1);
            background: rgba(255, 255, 255, 0.8);
            padding: 0.5rem 0.9rem;
            font-size: 0.94rem;
            font-weight: 800;
            color: var(--brand-ink);
            box-shadow: 0 4px 16px rgba(16, 32, 42, 0.03);
        }

        html.dark .dashboard-scan-pill {
            border-color: rgba(255, 255, 255, 0.1);
            background: rgba(255, 255, 255, 0.06);
            color: white;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
        }

        .dashboard-action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            height: 2.6rem;
            width: 2.6rem;
            border-radius: 0.85rem;
            border: 1px solid rgba(16, 32, 42, 0.08);
            background: rgba(255, 255, 255, 0.6);
            color: rgba(16, 32, 42, 0.7);
            transition: all 250ms ease;
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            cursor: pointer;
        }

        html.dark .dashboard-action-btn {
            border-color: rgba(255, 255, 255, 0.08);
            background: rgba(255, 255, 255, 0.04);
            color: rgba(231, 243, 244, 0.7);
        }

        .dashboard-action-btn:hover {
            transform: translateY(-3px) scale(1.05);
            border-color: rgba(18, 188, 200, 0.35);
            background: white;
            color: #0c757d;
            box-shadow: 0 12px 24px rgba(18, 188, 200, 0.15);
        }

        html.dark .dashboard-action-btn:hover {
            background: rgba(18, 188, 200, 0.2);
            color: #8be8ec;
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.35);
        }

        .dashboard-action-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 2.6rem));
            gap: 0.55rem;
            justify-content: start;
        }

        @media (min-width: 64rem) {
            .dashboard-table-head {
                display: grid;
                grid-template-columns: minmax(0, 1.4fr) 9rem minmax(0, 1.15fr) 7rem auto;
                gap: 1.5rem;
            }

            .dashboard-row {
                display: grid;
                grid-template-columns: minmax(0, 1.4fr) 9rem minmax(0, 1.15fr) 7rem auto;
                gap: 1.5rem;
            }

            .dashboard-action-grid {
                justify-content: end;
            }
        }
    </style>
@endpush

@section('content')
    @php
        $downloadUrlTemplate = $globalDepartmentMode
            ? route('qr.department.download', ['department' => $selectedDepartment, 'shortId' => '__SHORT_ID__'])
            : route('qr.download', ['shortId' => '__SHORT_ID__']);
    @endphp
    <section class="dashboard-shell page-shell flex flex-col gap-2.5 pb-2">
        <div class="dashboard-top">
            <div class="dashboard-title-area">
                <span class="eyebrow">Kontrol paneli</span>
                <h2
                    class="sf-display mt-2 text-[1.8rem] font-extrabold tracking-[-0.06em] text-brand-ink dark:text-white md:text-[2.2rem]">
                    QR Yönetimi
                </h2>
                <p class="mt-1.5 text-[0.82rem] leading-6 text-slate-600 dark:text-slate-300">
                    <span class="dashboard-department font-semibold">{{ $departmentName }}</span> için bağlantıları yönetin
                    ve listeyi hızla daraltın.
                </p>
            </div>

            <div class="dashboard-action-rail">
                <div class="month-mark dashboard-counter-pill text-[0.72rem]">Görünen kayıt: {{ $filteredQrCount }}</div>
                @if ($departmentHubUrl)
                    <a href="{{ $departmentHubUrl }}" class="ghost-button px-3.5 py-2 text-[0.8rem]">Birimlere Dön</a>
                @endif
                @if ($filtersActive)
                    <a href="{{ $resetFilterUrl }}" class="ghost-button px-3.5 py-2 text-[0.8rem]">Tümünü Göster</a>
                @endif
                <a href="{{ $createUrl }}" class="brand-button px-5 py-3 text-[0.9rem] shadow-lg shadow-cyan-500/20">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span>Yeni Bağlantı</span>
                </a>
            </div>
        </div>

        <div class="dashboard-metrics">
            <a href="{{ $activeFilterUrl }}#qr-list"
                class="dashboard-metric rounded-[1.16rem] p-3 md:p-3.5 {{ $activeFilterSelected ? 'is-active' : '' }}"
                aria-label="Aktif bağlantıları filtrele">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p
                            class="text-[0.68rem] font-extrabold uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400">
                            Aktif bağlantılar</p>
                        <div class="dashboard-metric-value">{{ $activeQrCount }}</div>
                    </div>
                    <div class="dashboard-metric-icon">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 7h16M4 12h10M4 17h7"></path>
                        </svg>
                    </div>
                </div>

                <p class="dashboard-metric-copy">
                    Yayındaki kayıtları ayırır.
                </p>

            </a>

            <a href="{{ $scannedFilterUrl }}#qr-list"
                class="dashboard-metric rounded-[1.16rem] p-3 md:p-3.5 {{ $scannedFilterSelected ? 'is-active' : '' }}"
                aria-label="Taranan kayıtları filtrele">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p
                            class="text-[0.68rem] font-extrabold uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400">
                            Toplam tarama</p>
                        <div class="dashboard-metric-value">{{ $totalScans }}</div>
                    </div>
                    <div class="dashboard-metric-icon">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3 3 7-7"></path>
                        </svg>
                    </div>
                </div>

                <p class="dashboard-metric-copy">
                    Taranan kayıtları öne alır.
                </p>

            </a>
        </div>

        <div id="qr-list" class="dashboard-list-card apple-glass-panel page-card rounded-[1.35rem]">
            <div class="dashboard-list-hero">
                <div>
                    <span
                        class="surface-chip inline-flex text-cyan-600 bg-cyan-500/10 dark:text-cyan-400 items-center rounded-full px-3.5 py-1.5 text-[0.72rem] font-extrabold uppercase tracking-[0.2em] text-slate-500 dark:text-slate-300">
                        {{ $filterTitle }}
                    </span>
                    <h3 class="mt-2 text-[1.18rem] font-bold tracking-[-0.05em] text-brand-ink dark:text-white">Kayıtlı
                        Bağlantılar</h3>
                    <p class="dashboard-head-copy">{{ $filterDescription }}</p>
                </div>
            </div>

            <div class="dashboard-list-scroll mt-3">
                <div
                    class="dashboard-table-head text-[0.78rem] font-extrabold uppercase tracking-[0.22em] text-slate-400 dark:text-slate-500">
                    <span>Bağlantı</span>
                    <span>Kod</span>
                    <span>Hedef URL</span>
                    <span>Tarama</span>
                    <span>İşlemler</span>
                </div>

                <div class="space-y-2">
                    @forelse ($qrCodes as $qrCode)
                        <article class="table-row dashboard-row rounded-[1.05rem] p-2.5 md:p-3">
                            <div class="min-w-0">
                                <p class="dashboard-row-title">{{ $qrCode->title }}</p>
                                <div class="dashboard-row-subtitle">
                                    <span>{{ $qrCode->department?->name }}</span>
                                    <span aria-hidden="true">&middot;</span>
                                    <span>{{ $qrCode->created_at?->format('d.m.Y H:i') }}</span>
                                </div>
                            </div>

                            <div class="flex items-center">
                                <span class="dashboard-link-code">{{ $qrCode->short_id }}</span>
                            </div>

                            <div class="min-w-0">
                                <div class="break-all text-[0.86rem] leading-6 text-slate-600 dark:text-slate-300 lg:truncate"
                                    title="{{ $qrCode->destination_url }}">
                                    {{ $qrCode->destination_url }}
                                </div>
                            </div>

                            <div class="flex items-center">
                                <span class="dashboard-scan-pill">{{ $qrCode->scans_count }}</span>
                            </div>

                            <div class="dashboard-action-grid">
                                <button type="button" onclick="openQRModal('{{ $qrCode->short_id }}', @js($qrCode->title))"
                                    class="dashboard-action-btn inline-flex  items-center justify-center rounded-full"
                                    title="Görüntüle">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                        aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                        </path>
                                    </svg>
                                </button>
                                <a href="{{ $globalDepartmentMode ? route('qr.department.download', ['department' => $selectedDepartment, 'shortId' => $qrCode->short_id]) : route('qr.download', $qrCode->short_id) }}"
                                    class="dashboard-action-btn inline-flex  items-center justify-center rounded-full"
                                    title="İndir">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                        aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                    </svg>
                                </a>
                                <a href="{{ $globalDepartmentMode ? route('qr.department.edit', ['department' => $selectedDepartment, 'shortId' => $qrCode->short_id]) : route('qr.edit', $qrCode->short_id) }}"
                                    class="dashboard-action-btn inline-flex  items-center justify-center rounded-full"
                                    title="Düzenle">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                        aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                        </path>
                                    </svg>
                                </a>
                                <a href="{{ $globalDepartmentMode ? route('qr.department.delete.confirm', ['department' => $selectedDepartment, 'shortId' => $qrCode->short_id]) : route('qr.delete.confirm', $qrCode->short_id) }}"
                                    class="dashboard-action-btn inline-flex  items-center justify-center rounded-full"
                                    title="Sil">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                        aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                        </path>
                                    </svg>
                                </a>
                            </div>
                        </article>
                    @empty
                        <div class="empty-state-shell rounded-[1.6rem] px-6 py-[4rem] text-center">
                            <div class="mx-auto flex max-w-md flex-col items-center gap-4">
                                <div
                                    class="surface-panel flex h-[4.5rem] w-[4.5rem] items-center justify-center rounded-full text-slate-400 dark:text-slate-500">
                                    <svg class="h-9 w-9" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                        aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4">
                                        </path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-lg font-semibold text-brand-ink dark:text-white">Henüz kayıt bulunmuyor</p>
                                    <p class="mt-2 text-[0.92rem] leading-7 text-slate-600 dark:text-slate-300">İlk bağlantınızı
                                        oluşturarak başlayın.</p>
                                </div>
                            </div>
                        </div>
                    @endforelse
                </div>

                @if($qrCodes->hasPages())
                    <div class="mt-6 flex justify-center pb-2">
                        {{ $qrCodes->links() }}
                    </div>
                @endif
            </div>
        </div>
    </section>

    <div id="qr-modal" class="fixed inset-0 z-[100] hidden items-center justify-center p-4">
        <div id="qr-modal-backdrop"
            class="absolute inset-0 bg-black/40 backdrop-blur-md opacity-0 backdrop-blur-sm transition-opacity"
            onclick="closeQRModal()"></div>

        <div id="qr-modal-content"
            class="page-card apple-glass-panel relative z-10 flex w-full max-w-md scale-95 flex-col items-center rounded-[2.5rem] border border-white p-8 text-center opacity-0 shadow-[0_24px_80px_rgba(16,32,42,0.16)] transition-all duration-300 dark:border-white/10">
            <button type="button" onclick="closeQRModal()"
                class="absolute right-4 top-4 rounded-full bg-white/50 p-2 text-slate-500 transition hover:bg-white hover:text-brand-ink dark:bg-white/8 dark:text-slate-300 dark:hover:bg-white/12 dark:hover:text-white">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>

            <h3 id="qr-modal-title"
                class="mb-6 line-clamp-2 text-xl font-bold leading-tight text-brand-ink dark:text-white">Yükleniyor...</h3>
            <p
                class="-mt-3 mb-5 text-[0.72rem] font-extrabold uppercase tracking-[0.24em] text-slate-500 dark:text-slate-400">
                Yunus Emre Enstitüsü kurumsal QR kartı
            </p>

            <div
                class="relative mb-6 flex aspect-[5/6] w-full items-center justify-center rounded-[1.9rem] border border-black/5 bg-[linear-gradient(180deg,rgba(255,255,255,0.92),rgba(239,248,249,0.9))] p-4 shadow-inner dark:border-white/10 dark:bg-[linear-gradient(180deg,rgba(255,255,255,0.06),rgba(18,188,200,0.08))]">
                <div id="qr-modal-loader" class="absolute inset-0 flex items-center justify-center">
                    <span class="relative flex h-3 w-3">
                        <span
                            class="absolute inline-flex h-full w-full animate-ping rounded-full bg-cyan-500 opacity-75"></span>
                        <span class="relative inline-flex h-3 w-3 rounded-full bg-cyan-500"></span>
                    </span>
                </div>
                <img id="qr-modal-img" src="" alt="QR kod" class="hidden h-full w-full rounded-[1.4rem] object-contain">
            </div>

            <div class="flex w-full flex-col gap-3">
                <div class="grid grid-cols-2 gap-3">
                    <a id="qr-modal-download" href="#" class="brand-button px-3 py-3 text-[0.8rem]" title="Orijinal format: Vektörel SVG">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        <span>SVG İndir</span>
                    </a>
                    <button type="button" onclick="downloadQrAsPng()" id="qr-modal-png-btn" class="brand-button px-3 py-3 text-[0.8rem] bg-[#0c757d] hover:bg-[#07474d] text-white shadow-md border-0" title="Sosyal Medya formatı: Keskin PNG">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <span>PNG Kaydet</span>
                    </button>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <button type="button" onclick="printQr()" class="ghost-button px-4 py-3 text-[0.8rem]">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                        </svg>
                        <span>Yazdır</span>
                    </button>
                    <button type="button" onclick="closeQRModal()" class="ghost-button px-4 py-3 text-[0.8rem]">Kapat</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const qrDownloadUrlTemplate = @js($downloadUrlTemplate);

        function openQRModal(shortId, title) {
            const modal = document.getElementById('qr-modal');
            const backdrop = document.getElementById('qr-modal-backdrop');
            const content = document.getElementById('qr-modal-content');
            const titleEl = document.getElementById('qr-modal-title');
            const imgEl = document.getElementById('qr-modal-img');
            const loader = document.getElementById('qr-modal-loader');
            const downloadBtn = document.getElementById('qr-modal-download');

            imgEl.classList.add('hidden');
            loader.classList.remove('hidden');
            titleEl.classList.remove('text-rose-500');
            titleEl.textContent = title;
            downloadBtn.href = qrDownloadUrlTemplate.replace('__SHORT_ID__', shortId);
            imgEl.src = downloadBtn.href + '?inline=1';

            imgEl.onload = function () {
                loader.classList.add('hidden');
                imgEl.classList.remove('hidden');
            };
            imgEl.onerror = function () {
                loader.classList.add('hidden');
                imgEl.classList.add('hidden');
                titleEl.textContent = 'Görsel yüklenemedi (tarayıcı veya sunucu hatası)';
                titleEl.classList.add('text-rose-500');
            };

            modal.classList.remove('hidden');
            modal.classList.add('flex');

            setTimeout(() => {
                backdrop.classList.remove('opacity-0');
                backdrop.classList.add('opacity-100');
                content.classList.remove('scale-95', 'opacity-0');
                content.classList.add('scale-100', 'opacity-100');
            }, 10);
        }

        function closeQRModal() {
            const modal = document.getElementById('qr-modal');
            const backdrop = document.getElementById('qr-modal-backdrop');
            const content = document.getElementById('qr-modal-content');

            backdrop.classList.remove('opacity-100');
            backdrop.classList.add('opacity-0');
            content.classList.remove('scale-100', 'opacity-100');
            content.classList.add('scale-95', 'opacity-0');

            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                document.getElementById('qr-modal-img').src = '';
            }, 300);
        }

        function downloadQrAsPng() {
            const btn = document.getElementById('qr-modal-png-btn');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<span class="mr-2 h-4 w-4 rounded-full border-2 border-white animate-spin border-r-transparent"></span> İşleniyor...';
            btn.classList.add('opacity-80', 'pointer-events-none');

            const svgImg = document.getElementById('qr-modal-img');
            const canvas = document.createElement('canvas');
            
            canvas.width = 720;
            canvas.height = 1040;
            const ctx = canvas.getContext('2d');
            
            const imgObj = new Image();
            imgObj.crossOrigin = 'Anonymous';
            
            imgObj.onload = () => {
                ctx.drawImage(imgObj, 0, 0, canvas.width, canvas.height);
                const a = document.createElement('a');
                const cleanTitle = document.getElementById('qr-modal-title').textContent.replace(/[^a-zA-Z0-9-_\.]/g, '').substring(0,20) || 'QR';
                a.download = `Yunus-Emre-QR-${cleanTitle}.png`;
                a.href = canvas.toDataURL('image/png', 1.0);
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                
                btn.innerHTML = originalText;
                btn.classList.remove('opacity-80', 'pointer-events-none');
            };
            
            imgObj.onerror = () => {
                alert('PNG dönüştürme işlemi tarayıcı engeline takıldı. Lütfen SVG İndir seçeneğini kullanın.');
                btn.innerHTML = originalText;
                btn.classList.remove('opacity-80', 'pointer-events-none');
            };
            
            fetch(svgImg.src)
                .then(response => response.blob())
                .then(blob => {
                    const reader = new FileReader();
                    reader.onloadend = function() {
                        imgObj.src = reader.result;
                    }
                    reader.readAsDataURL(blob);
                })
                .catch(() => {
                    imgObj.src = svgImg.src;
                });
        }

        function printQr() {
            const img = document.getElementById('qr-modal-img');
            if (!img.src) return;
            const printWin = window.open('', '_blank');
            printWin.document.write(`
                <html>
                    <head>
                        <title>QR Kodu Yazdır</title>
                        <style>
                            @page { margin: 0; size: auto; }
                            body { margin: 0; display: flex; justify-content: center; align-items: center; min-height: 100vh; background: white; font-family: sans-serif; }
                            img { max-width: 100%; max-height: 98vh; object-fit: contain; }
                        </style>
                    </head>
                    <body>
                        <img src="${img.src}" />
                        <script>
                            window.onload = function() { window.print(); setTimeout(window.close, 1000); }
                        <\/script>
                    </body>
                </html>
            `);
            printWin.document.close();
        }
    </script>
@endpush