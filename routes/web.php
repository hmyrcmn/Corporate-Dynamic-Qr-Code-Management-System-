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
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/departments/{department}', [DashboardController::class, 'showDepartment'])->name('dashboard.department');
    Route::get('/dashboard/create', [QrCodeController::class, 'create'])->name('qr.create');
    Route::post('/dashboard/create', [QrCodeController::class, 'store'])->name('qr.store');
    Route::get('/dashboard/departments/{department}/create', [QrCodeController::class, 'createForDepartment'])->name('qr.department.create');
    Route::post('/dashboard/departments/{department}/create', [QrCodeController::class, 'storeForDepartment'])->name('qr.department.store');
    Route::get('/dashboard/edit/{shortId}', [QrCodeController::class, 'edit'])->name('qr.edit');
    Route::put('/dashboard/edit/{shortId}', [QrCodeController::class, 'update'])->name('qr.update');
    Route::get('/dashboard/departments/{department}/edit/{shortId}', [QrCodeController::class, 'editForDepartment'])->name('qr.department.edit');
    Route::put('/dashboard/departments/{department}/edit/{shortId}', [QrCodeController::class, 'updateForDepartment'])->name('qr.department.update');
    Route::get('/dashboard/delete/{shortId}', [QrCodeController::class, 'confirmDelete'])->name('qr.delete.confirm');
    Route::delete('/dashboard/delete/{shortId}', [QrCodeController::class, 'destroy'])->name('qr.delete');
    Route::get('/dashboard/departments/{department}/delete/{shortId}', [QrCodeController::class, 'confirmDeleteForDepartment'])->name('qr.department.delete.confirm');
    Route::delete('/dashboard/departments/{department}/delete/{shortId}', [QrCodeController::class, 'destroyForDepartment'])->name('qr.department.delete');
    Route::get('/download-qr/{shortId}', [QrCodeController::class, 'download'])->name('qr.download');
    Route::get('/dashboard/departments/{department}/download/{shortId}', [QrCodeController::class, 'downloadForDepartment'])->name('qr.department.download');
});

Route::get('/{shortId}', RedirectController::class)->name('qr.redirect');
