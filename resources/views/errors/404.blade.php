@extends('layouts.app')

@section('title', 'Sayfa Bulunamadı | Dinamik QR')

@section('content')
    <section class="page-shell page-shell-md flex min-h-[65svh] items-center pb-8">
        <div class="page-card apple-glass-heavy w-full rounded-[2.5rem] p-6 text-center md:p-10">
            <span class="eyebrow justify-center">404</span>
            <h1 class="sf-display mt-4 text-3xl font-extrabold text-brand-ink dark:text-white md:text-5xl">Aradığınız sayfa bulunamadı.</h1>
            <p class="mx-auto mt-4 max-w-xl text-sm leading-7 text-slate-600 dark:text-slate-300 md:text-base">
                Link kaldırılmış olabilir ya da kısa QR adresi hatalı olabilir. Ana sayfaya dönerek yeni bir akış başlatabilirsiniz.
            </p>

            <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
                <a href="{{ route('landing') }}" class="brand-button">Ana Sayfaya Dön</a>
                @auth
                    <a href="{{ route('dashboard') }}" class="ghost-button">Panele Git</a>
                @else
                    <a href="{{ route('login') }}" class="ghost-button">Kurumsal Giriş</a>
                @endauth
            </div>
        </div>
    </section>
@endsection
