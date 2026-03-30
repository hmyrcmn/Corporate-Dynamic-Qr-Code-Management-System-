@extends('layouts.app')

@section('title', $pageTitle.' | Dinamik QR')

@push('styles')
    <style>
        .qr-form-shell {
            width: min(100%, 45rem);
            margin-inline: auto;
        }

        .qr-form-breadcrumb {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            font-size: 0.8rem;
            font-weight: 800;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            color: var(--eyebrow-color);
            margin-bottom: 1.25rem;
        }

        .qr-form-breadcrumb-current {
            color: var(--accent-text);
        }

        .apple-glass-panel {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.65), rgba(255, 255, 255, 0.45));
            backdrop-filter: blur(32px);
            -webkit-backdrop-filter: blur(32px);
            border: 1px solid rgba(255, 255, 255, 0.7);
            border-radius: 2.2rem;
            box-shadow: 0 32px 64px rgba(31, 47, 50, 0.1), inset 0 1px 0 rgba(255, 255, 255, 0.9);
            overflow: hidden;
        }

        html.dark .apple-glass-panel {
            background: linear-gradient(135deg, rgba(20, 30, 35, 0.65), rgba(10, 15, 20, 0.45));
            border-color: rgba(255, 255, 255, 0.08);
            box-shadow: 0 32px 64px rgba(0, 0, 0, 0.4), inset 0 1px 0 rgba(255, 255, 255, 0.05);
        }

        .qr-form-header {
            padding: 2rem 2.2rem 1.75rem;
            position: relative;
        }

        .qr-form-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 2.2rem;
            right: 2.2rem;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(16, 32, 42, 0.08), transparent);
        }

        html.dark .qr-form-header::after {
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.08), transparent);
        }

        .qr-form-header-row {
            display: flex;
            align-items: flex-start;
            gap: 1.25rem;
        }

        .qr-form-icon {
            display: inline-flex;
            height: 3.5rem;
            width: 3.5rem;
            flex-shrink: 0;
            align-items: center;
            justify-content: center;
            border-radius: 1.1rem;
            background: linear-gradient(135deg, rgba(49, 192, 207, 1) 0%, rgba(28, 171, 187, 1) 100%);
            color: white;
            box-shadow: 0 16px 32px rgba(18, 188, 200, 0.25), inset 0 2px 0 rgba(255, 255, 255, 0.3);
        }

        .qr-form-icon svg {
            height: 1.5rem;
            width: 1.5rem;
        }

        .qr-form-title {
            font-size: clamp(1.6rem, 2.5vw, 2.2rem);
            line-height: 1.1;
            letter-spacing: -0.05em;
        }

        .qr-form-copy {
            max-width: 30rem;
            font-size: 0.9rem;
            line-height: 1.6;
            margin-top: 0.5rem;
        }

        .qr-form-body {
            padding: 1.75rem 2.2rem 2rem;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.3), transparent);
        }

        html.dark .qr-form-body {
            background: linear-gradient(180deg, rgba(0, 0, 0, 0.1), transparent);
        }

        .qr-form-stack {
            display: grid;
            gap: 1.5rem;
        }

        .qr-form-field-label {
            display: block;
            margin-bottom: 0.6rem;
            font-size: 0.85rem;
            font-weight: 800;
            letter-spacing: 0.02em;
            color: var(--brand-ink);
        }

        html.dark .qr-form-field-label {
            color: rgba(231, 243, 244, 0.9);
        }

        .qr-form-input {
            width: 100%;
            min-height: 3.6rem;
            border-radius: 1.2rem;
            padding-inline: 1.25rem;
            font-size: 0.95rem;
            font-weight: 600;
            background: rgba(255, 255, 255, 0.7);
            border: 1px solid rgba(16, 32, 42, 0.08);
            box-shadow: 0 4px 12px rgba(16, 32, 42, 0.02), inset 0 2px 4px rgba(16, 32, 42, 0.02);
            color: var(--brand-ink);
            transition: all 250ms ease;
        }

        html.dark .qr-form-input {
            background: rgba(0, 0, 0, 0.2);
            border-color: rgba(255, 255, 255, 0.08);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2), inset 0 2px 4px rgba(0, 0, 0, 0.2);
            color: white;
        }

        .qr-form-input:focus {
            outline: none;
            background: white;
            border-color: rgba(18, 188, 200, 0.5);
            box-shadow: 0 0 0 3px rgba(18, 188, 200, 0.15), 0 8px 24px rgba(18, 188, 200, 0.1);
        }

        html.dark .qr-form-input:focus {
            background: rgba(0, 0, 0, 0.4);
            border-color: rgba(18, 188, 200, 0.5);
            box-shadow: 0 0 0 3px rgba(18, 188, 200, 0.25), 0 8px 24px rgba(0, 0, 0, 0.4);
        }

        .qr-form-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding-top: 1.5rem;
            margin-top: 1rem;
            border-top: 1px solid rgba(16, 32, 42, 0.06);
        }

        html.dark .qr-form-actions {
            border-top-color: rgba(255, 255, 255, 0.06);
        }

        .qr-form-submit {
            min-height: 3.2rem;
            flex: 1 1 auto;
            font-size: 0.95rem;
            border-radius: 1rem;
            box-shadow: 0 12px 24px rgba(18, 188, 200, 0.25);
        }

        .qr-form-cancel {
            min-height: 3.2rem;
            min-width: 7rem;
            padding-inline: 1.5rem;
            font-size: 0.95rem;
            border-radius: 1rem;
        }

        .qr-form-meta {
            display: grid;
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .qr-form-meta-card {
            border-radius: 1.2rem;
            padding: 1rem 1.25rem;
            background: rgba(255, 255, 255, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.8);
            box-shadow: 0 8px 16px rgba(16, 32, 42, 0.03);
            backdrop-filter: blur(12px);
        }

        html.dark .qr-form-meta-card {
            background: rgba(255, 255, 255, 0.03);
            border-color: rgba(255, 255, 255, 0.08);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }

        .qr-form-status {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            cursor: pointer;
            transition: all 200ms ease;
        }

        .qr-form-status:hover {
            background: rgba(255, 255, 255, 0.8);
        }

        html.dark .qr-form-status:hover {
            background: rgba(255, 255, 255, 0.05);
        }

        /* Apple style toggle switch */
        .qr-form-status-toggle {
            appearance: none;
            width: 3.2rem;
            height: 1.8rem;
            background: rgba(16, 32, 42, 0.2);
            border-radius: 999px;
            position: relative;
            cursor: pointer;
            outline: none;
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.1);
            transition: background 300ms ease;
            margin: 0;
        }

        html.dark .qr-form-status-toggle {
            background: rgba(255, 255, 255, 0.15);
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.3);
        }

        .qr-form-status-toggle::after {
            content: '';
            position: absolute;
            top: 0.15rem;
            left: 0.15rem;
            width: 1.5rem;
            height: 1.5rem;
            background: white;
            border-radius: 50%;
            border: 1px solid rgba(0,0,0,0.05);
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
            transition: transform 300ms cubic-bezier(0.4, 0.0, 0.2, 1);
        }

        .qr-form-status-toggle:checked {
            background: #12bcc8;
        }

        .qr-form-status-toggle:checked::after {
            transform: translateX(1.4rem);
        }

        .qr-form-tools {
            margin-top: 1.5rem;
            background: rgba(255, 255, 255, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.6);
            backdrop-filter: blur(16px);
            border-radius: 1.2rem;
            padding: 1rem 1.25rem;
        }

        html.dark .qr-form-tools {
            background: rgba(255, 255, 255, 0.02);
            border-color: rgba(255, 255, 255, 0.05);
        }

        @media (min-width: 48rem) {
            .qr-form-stack {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
            .qr-form-meta {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (min-width: 64rem) {
            .qr-form-page {
                min-height: calc(100svh - 7.4rem);
                align-items: flex-start;
            }

            .qr-form-shell {
                width: min(100%, 41.5rem);
            }

            .qr-form-breadcrumb {
                margin-bottom: 0.9rem;
            }

            .qr-form-header {
                padding: 1.45rem 1.65rem 1.15rem;
            }

            .qr-form-header::after {
                left: 1.65rem;
                right: 1.65rem;
            }

            .qr-form-header-row {
                gap: 1rem;
            }

            .qr-form-icon {
                height: 3rem;
                width: 3rem;
            }

            .qr-form-icon svg {
                height: 1.3rem;
                width: 1.3rem;
            }

            .qr-form-title {
                font-size: clamp(1.45rem, 2vw, 1.9rem);
            }

            .qr-form-copy {
                font-size: 0.84rem;
                line-height: 1.5;
                margin-top: 0.35rem;
            }

            .qr-form-body {
                padding: 1.3rem 1.65rem 1.45rem;
            }

            .qr-form-stack {
                gap: 1rem;
            }

            .qr-form-field-label {
                margin-bottom: 0.45rem;
                font-size: 0.8rem;
            }

            .qr-form-input {
                min-height: 3.2rem;
                padding-inline: 1rem;
                font-size: 0.9rem;
            }

            .qr-form-meta {
                gap: 0.8rem;
                margin-top: 1rem;
            }

            .qr-form-meta-card,
            .qr-form-tools {
                padding: 0.85rem 1rem;
            }

            .qr-form-actions {
                gap: 0.8rem;
                padding-top: 1rem;
                margin-top: 0.8rem;
            }

            .qr-form-submit,
            .qr-form-cancel {
                min-height: 2.9rem;
                font-size: 0.88rem;
            }
        }

        @media (max-width: 63.999rem) {
            .qr-form-header,
            .qr-form-body {
                padding-inline: 1.5rem;
            }

            .qr-form-header {
                padding-top: 1.5rem;
                padding-bottom: 1.25rem;
            }

            .qr-form-body {
                padding-top: 1.25rem;
                padding-bottom: 1.5rem;
            }
            
            .qr-form-actions {
                flex-direction: column;
                align-items: stretch;
            }

            .qr-form-cancel {
                width: 100%;
            }
        }
    </style>
@endpush

@section('content')
    <section class="qr-form-page page-shell page-shell-md pb-3 lg:flex">
        <div class="qr-form-shell">
            <div class="qr-form-breadcrumb">
                <span>Yonetim Paneli</span>
                <span>/</span>
                <span class="qr-form-breadcrumb-current">{{ $qrCode->exists ? 'Kayit Duzenle' : 'Yeni Kayit' }}</span>
            </div>

            <div class="qr-form-card apple-glass-panel mt-3">
                <div class="qr-form-header">
                    <div class="qr-form-header-row">
                        <div class="qr-form-icon">
                            @if ($qrCode->exists)
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9 11l6.768-6.768a2.5 2.5 0 113.536 3.536L12.536 14.5a4 4 0 01-1.697 1.03L7 16l.47-3.839A4 4 0 018.5 10.464z"></path>
                                </svg>
                            @else
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                            @endif
                        </div>

                        <div class="min-w-0">
                            <h1 class="qr-form-title sf-display font-extrabold text-brand-ink dark:text-white">{{ $pageTitle }}</h1>
                            <p class="qr-form-copy mt-2.5 text-slate-600 dark:text-slate-300">
                                Sistem uzerinde yonlendirilecek {{ $qrCode->exists ? 'kaydi guncelleyin' : 'yeni bir baglanti olusturun' }}. Hedef URL ve diger bilgileri eksiksiz doldurunuz.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="qr-form-body">
                    @if ($errors->any())
                        <div class="rounded-[1rem] border border-rose-200 bg-rose-50 px-3 py-2.5 text-[0.78rem] font-semibold text-rose-600 dark:border-rose-400/20 dark:bg-rose-500/10 dark:text-rose-200">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <form action="{{ $formAction }}" method="post" class="{{ $errors->any() ? 'mt-3' : '' }}">
                        @csrf
                        @if ($qrCode->exists)
                            @method('PUT')
                        @endif

                        <div class="qr-form-stack">
                            @if(auth()->user()->hasGlobalAccess())
                                <div class="col-span-full mb-1">
                                    <label for="department_id" class="qr-form-field-label">Birim (Admin)</label>
                                    <select id="department_id" name="department_id" required class="field-shell qr-form-input appearance-none bg-no-repeat bg-[right_1.25rem_center] bg-[length:1.2em_1.2em]" style="background-image: url('data:image/svg+xml;charset=UTF-8,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22currentColor%22 stroke-width=%222%22 stroke-linecap=%22round%22 stroke-linejoin=%22round%22%3E%3Cpolyline points=%226 9 12 15 18 9%22/%3E%3C/svg%3E');">
                                        <option value="">Birim Seciniz</option>
                                        @foreach($departments as $dept)
                                            <option value="{{ $dept->id }}" @selected(old('department_id', $qrCode->department_id) == $dept->id)>
                                                {{ $dept->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('department_id')
                                        <span class="mt-1 block text-[0.78rem] font-semibold text-rose-500">{{ $message }}</span>
                                    @enderror
                                </div>
                            @endif

                            <div>
                                <label for="title" class="qr-form-field-label">Baslik</label>
                                <input type="text" id="title" name="title" required maxlength="255" value="{{ old('title', $qrCode->title) }}" class="field-shell qr-form-input" placeholder="Orn: Yaz Kursu Katalogu">
                            </div>

                            <div>
                                <label for="destination_url" class="qr-form-field-label">Hedef URL</label>
                                <input type="url" id="destination_url" name="destination_url" required value="{{ old('destination_url', $qrCode->destination_url) }}" class="field-shell qr-form-input" placeholder="https://yee.org.tr/...">
                            </div>
                        </div>

                        @if ($qrCode->exists)
                            <div class="qr-form-meta">
                                <div class="surface-panel qr-form-meta-card">
                                    <p class="field-label mb-2">Kisa kod</p>
                                    <p class="text-[0.84rem] font-semibold text-brand-ink dark:text-white">{{ $qrCode->short_id ?: 'Otomatik uretilecek' }}</p>
                                </div>

                                <label class="surface-panel qr-form-meta-card qr-form-status cursor-pointer">
                                    <div>
                                        <p class="field-label mb-2">Durum</p>
                                        <p class="text-[0.84rem] font-semibold text-brand-ink dark:text-white">Yayinda tut</p>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <input type="hidden" name="is_active" value="0">
                                        <input type="checkbox" name="is_active" value="1" class="qr-form-status-toggle" @checked(old('is_active', $qrCode->is_active ?? true))>
                                    </div>
                                </label>
                            </div>
                        @else
                            <input type="hidden" name="is_active" value="1">
                        @endif

                        <div class="qr-form-actions">
                            <button type="submit" class="primary-button qr-form-submit">
                                <span>{{ $submitLabel }}</span>
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                                </svg>
                            </button>

                            <a href="{{ route('dashboard') }}" class="ghost-button qr-form-cancel">Iptal</a>
                        </div>

                        @if ($qrCode->exists)
                            <div class="qr-form-tools soft-card rounded-[1.05rem] p-3">
                                <div class="flex flex-col gap-2.5 md:flex-row md:items-center md:justify-between">
                                    <div>
                                        <p class="text-[0.82rem] font-semibold text-brand-ink dark:text-white">Kayit bilgisi</p>
                                        <p class="mt-1 text-[0.76rem] text-slate-600 dark:text-slate-300">
                                            Birim: {{ $qrCode->department?->name ?? 'Atanmamis' }} &middot; Olusturan: {{ $qrCode->creator?->name ?? $qrCode->creator?->username ?? 'Bilinmiyor' }}
                                        </p>
                                    </div>

                                    <div class="flex flex-wrap gap-2.5">
                                        <a href="{{ route('qr.download', $qrCode->short_id) }}" class="ghost-button px-3.5 py-2 text-[0.78rem]">QR Indir</a>
                                        <a href="{{ route('qr.delete.confirm', $qrCode->short_id) }}" class="danger-button px-3.5 py-2 text-[0.78rem]">Kaydi Sil</a>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection
