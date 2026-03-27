<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\QrCodeController;
use App\Http\Controllers\RedirectController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'landing')->name('landing');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/dashboard/create', [QrCodeController::class, 'create'])->name('qr.create');
    Route::post('/dashboard/create', [QrCodeController::class, 'store'])->name('qr.store');
    Route::get('/dashboard/edit/{shortId}', [QrCodeController::class, 'edit'])->name('qr.edit');
    Route::put('/dashboard/edit/{shortId}', [QrCodeController::class, 'update'])->name('qr.update');
    Route::get('/dashboard/delete/{shortId}', [QrCodeController::class, 'confirmDelete'])->name('qr.delete.confirm');
    Route::delete('/dashboard/delete/{shortId}', [QrCodeController::class, 'destroy'])->name('qr.delete');
    Route::get('/download-qr/{shortId}', [QrCodeController::class, 'download'])->name('qr.download');
});

Route::get('/{shortId}', RedirectController::class)->name('qr.redirect');
