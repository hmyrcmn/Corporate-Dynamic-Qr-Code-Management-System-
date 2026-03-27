<?php

namespace App\Http\Controllers;

use App\Models\QrCode;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        $baseQuery = QrCode::query()
            ->with(['department', 'creator'])
            ->withCount('scans')
            ->accessibleTo($user);

        $summary = (clone $baseQuery)->get();
        $activeSelected = $request->boolean('active');
        $scannedSelected = $request->boolean('scanned');

        $qrCodes = (clone $baseQuery);

        if ($activeSelected) {
            $qrCodes->where('is_active', true);
        }

        if ($scannedSelected) {
            $qrCodes->whereHas('scans');
        }

        $qrCodes = $qrCodes
            ->orderByDesc($scannedSelected ? 'scans_count' : 'created_at')
            ->orderByDesc('created_at')
            ->get();

        if ($activeSelected && $scannedSelected) {
            $filterTitle = 'Aktif ve taranan kayitlar';
            $filterDescription = 'Yalnizca aktif olan ve en az bir kez taranan baglantilar listeleniyor.';
        } elseif ($activeSelected) {
            $filterTitle = 'Aktif kayitlar';
            $filterDescription = 'Sadece yayinda kalan baglantilar gorunuyor.';
        } elseif ($scannedSelected) {
            $filterTitle = 'Taranan kayitlar';
            $filterDescription = 'En az bir kez taranan baglantilar one cikiyor.';
        } else {
            $filterTitle = 'Tum kayitlar';
            $filterDescription = 'Tum baglantilar, hedef adresler ve islemler tek alanda listelenir.';
        }

        return view('dashboard.index', [
            'qrCodes' => $qrCodes,
            'allQrCount' => $summary->count(),
            'activeQrCount' => $summary->where('is_active', true)->count(),
            'totalScans' => $summary->sum('scans_count'),
            'filteredQrCount' => $qrCodes->count(),
            'filterTitle' => $filterTitle,
            'filterDescription' => $filterDescription,
            'activeFilterSelected' => $activeSelected,
            'scannedFilterSelected' => $scannedSelected,
            'filtersActive' => $activeSelected || $scannedSelected,
            'activeFilterUrl' => route('dashboard', array_filter([
                'active' => $activeSelected ? null : 1,
                'scanned' => $scannedSelected ? 1 : null,
            ])),
            'scannedFilterUrl' => route('dashboard', array_filter([
                'active' => $activeSelected ? 1 : null,
                'scanned' => $scannedSelected ? null : 1,
            ])),
            'resetFilterUrl' => route('dashboard'),
            'departmentName' => $user->hasGlobalAccess()
                ? 'Tum Birimler'
                : ($user->department?->name ?? 'Atanmamis Birim'),
        ]);
    }
}
