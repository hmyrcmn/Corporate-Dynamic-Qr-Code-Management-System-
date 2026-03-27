@extends('layouts.app')

@section('title', 'Kaydi Sil | Dinamik QR')

@section('content')
    <section class="page-shell page-shell-sm pb-6">
        <div class="page-card apple-glass-heavy rounded-[2rem] p-5 md:p-8">
            <span class="eyebrow">Kaydi sil</span>
            <h1 class="sf-display mt-3 text-2xl font-extrabold text-brand-ink dark:text-white md:text-[2rem]">Bu kayit silinecek</h1>
            <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-300">
                Islem geri alinmaz.
            </p>

            <div class="surface-panel mt-6 rounded-[1.75rem] p-5">
                <div class="space-y-3">
                    <div>
                        <p class="text-[11px] font-extrabold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Baslik</p>
                        <p class="mt-1 text-base font-semibold text-brand-ink dark:text-white">{{ $qrCode->title }}</p>
                    </div>
                    <div>
                        <p class="text-[11px] font-extrabold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Kisa kod</p>
                        <p class="mt-1 text-sm font-semibold text-cyan-600">{{ $qrCode->short_id }}</p>
                    </div>
                    <div>
                        <p class="text-[11px] font-extrabold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Hedef URL</p>
                        <p class="mt-1 break-all text-sm text-slate-600 dark:text-slate-300">{{ $qrCode->destination_url }}</p>
                    </div>
                </div>
            </div>

            <form action="{{ route('qr.delete', $qrCode->short_id) }}" method="post" class="mt-6 flex flex-col gap-3 sm:flex-row sm:justify-end">
                @csrf
                @method('DELETE')
                <a href="{{ route('dashboard') }}" class="ghost-button">Vazgec</a>
                <button type="submit" class="danger-button">Kaydi Kalici Olarak Sil</button>
            </form>
        </div>
    </section>
@endsection
