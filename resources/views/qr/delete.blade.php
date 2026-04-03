@extends('layouts.app')

@section('title', 'Kayd&#305; Sil | Dinamik QR')

@push('styles')
    <style>
        .delete-modal-stage {
            width: min(100%, 36rem);
            margin-inline: auto;
        }

        .delete-modal-card {
            position: relative;
            overflow: hidden;
            border-radius: 2rem;
            background:
                radial-gradient(circle at top right, rgba(123, 228, 232, 0.16), transparent 30%),
                linear-gradient(145deg, rgba(255, 255, 255, 0.78), rgba(255, 255, 255, 0.52));
            border: 1px solid rgba(255, 255, 255, 0.78);
            box-shadow: 0 28px 70px rgba(16, 32, 42, 0.12), inset 0 1px 0 rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(28px);
            -webkit-backdrop-filter: blur(28px);
        }

        html.dark .delete-modal-card {
            background:
                radial-gradient(circle at top right, rgba(123, 228, 232, 0.1), transparent 30%),
                linear-gradient(145deg, rgba(24, 35, 40, 0.76), rgba(11, 17, 22, 0.56));
            border-color: rgba(255, 255, 255, 0.08);
            box-shadow: 0 28px 70px rgba(0, 0, 0, 0.36), inset 0 1px 0 rgba(255, 255, 255, 0.05);
        }

        .delete-modal-icon {
            display: inline-flex;
            height: 3.6rem;
            width: 3.6rem;
            align-items: center;
            justify-content: center;
            border-radius: 1.2rem;
            background: linear-gradient(145deg, rgba(244, 63, 94, 0.14), rgba(244, 63, 94, 0.06));
            color: #e11d48;
            border: 1px solid rgba(244, 63, 94, 0.16);
        }

        html.dark .delete-modal-icon {
            background: linear-gradient(145deg, rgba(244, 63, 94, 0.2), rgba(244, 63, 94, 0.08));
            border-color: rgba(244, 63, 94, 0.18);
            color: #fda4af;
        }

        .delete-modal-meta {
            display: grid;
            gap: 0.8rem;
        }

        .delete-modal-item {
            border-radius: 1.25rem;
            padding: 0.95rem 1rem;
            background: rgba(255, 255, 255, 0.64);
            border: 1px solid rgba(255, 255, 255, 0.82);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.75);
        }

        html.dark .delete-modal-item {
            background: rgba(255, 255, 255, 0.04);
            border-color: rgba(255, 255, 255, 0.08);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.04);
        }

        .delete-modal-label {
            font-size: 0.68rem;
            font-weight: 800;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: rgba(16, 32, 42, 0.46);
        }

        html.dark .delete-modal-label {
            color: rgba(231, 243, 244, 0.46);
        }

        .delete-modal-value {
            margin-top: 0.35rem;
            font-size: 0.95rem;
            line-height: 1.65;
            color: var(--brand-ink);
            word-break: break-word;
        }

        html.dark .delete-modal-value {
            color: white;
        }
    </style>
@endpush

@section('content')
    <section class="page-shell page-shell-sm flex min-h-[72svh] items-center justify-center pb-8">
        <div class="delete-modal-stage">
            <div class="delete-modal-card p-5 md:p-7">
                <div class="mb-5 flex items-start justify-between gap-4">
                    <div class="flex items-start gap-4">
                        <div class="delete-modal-icon">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </div>

                        <div>
                            <span class="eyebrow">Silme Onay&#305;</span>
                            <h1 class="sf-display mt-2 text-[1.7rem] font-extrabold tracking-[-0.06em] text-brand-ink dark:text-white md:text-[2rem]">
                                Bu kayd&#305; silmek istiyor musunuz?
                            </h1>
                            <p class="mt-2 max-w-[28rem] text-[0.9rem] leading-7 text-slate-600 dark:text-slate-300">
                                Bu i&#351;lem geri al&#305;namaz. Kay&#305;t ve ba&#287;l&#305; y&#246;nlendirme kal&#305;c&#305; olarak kald&#305;r&#305;l&#305;r.
                            </p>
                        </div>
                    </div>

                    <a href="{{ $backUrl }}" class="ghost-button px-3 py-2 text-[0.78rem]">Kapat</a>
                </div>

                <div class="delete-modal-meta">
                    <div class="delete-modal-item">
                        <div class="delete-modal-label">Ba&#351;l&#305;k</div>
                        <div class="delete-modal-value">{{ $qrCode->title }}</div>
                    </div>

                    <div class="grid gap-3 md:grid-cols-[10rem_minmax(0,1fr)]">
                        <div class="delete-modal-item">
                            <div class="delete-modal-label">K&#305;sa Kod</div>
                            <div class="delete-modal-value text-cyan-700 dark:text-cyan-300">{{ $qrCode->short_id }}</div>
                        </div>

                        <div class="delete-modal-item">
                            <div class="delete-modal-label">Hedef URL</div>
                            <div class="delete-modal-value">{{ $qrCode->destination_url }}</div>
                        </div>
                    </div>
                </div>

                <form action="{{ $deleteAction }}" method="post" class="mt-6 flex flex-col gap-3 sm:flex-row sm:justify-end">
                    @csrf
                    @method('DELETE')
                    <a href="{{ $backUrl }}" class="ghost-button px-5 py-3">Vazge&ccedil;</a>
                    <button type="submit" class="danger-button px-5 py-3">Kayd&#305; Kal&#305;c&#305; Olarak Sil</button>
                </form>
            </div>
        </div>
    </section>
@endsection
