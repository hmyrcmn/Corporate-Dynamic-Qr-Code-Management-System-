<?php

namespace App\Http\Controllers;

use App\Models\QrCode;
use App\Models\ScanAnalytics;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $activeSelected = $request->boolean('active');
        $scannedSelected = $request->boolean('scanned');

        $summary = Cache::remember(
            sprintf('dashboard-summary:%s', $user->getAuthIdentifier()),
            now()->addSeconds(30),
            function () use ($user): array {
                $accessibleQrCodes = QrCode::query()->accessibleTo($user);

                $totalScans = ScanAnalytics::query()
                    ->join('qr_codes', 'qr_codes.id', '=', 'scan_analytics.qr_code_id')
                    ->when(
                        ! $user->hasGlobalAccess(),
                        fn (Builder $query): Builder => $query->where('qr_codes.department_id', $user->department_id ?? 0),
                    )
                    ->count();

                return [
                    'allQrCount' => (clone $accessibleQrCodes)->count(),
                    'activeQrCount' => (clone $accessibleQrCodes)->where('is_active', true)->count(),
                    'totalScans' => $totalScans,
                ];
            },
        );

        $qrCodesQuery = $this->applyFilters(
            QrCode::query()
                ->with([
                    'department:id,name',
                    'creator:id,name,username',
                ])
                ->withCount('scans')
                ->accessibleTo($user),
            $activeSelected,
            $scannedSelected,
        );

        if ($scannedSelected) {
            $qrCodesQuery->orderByDesc('scans_count');
        }

        $qrCodes = $qrCodesQuery
            ->orderByDesc('created_at')
            ->paginate(30)
            ->withQueryString();

        $filteredQrCount = $qrCodes->total();

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
            'allQrCount' => $summary['allQrCount'],
            'activeQrCount' => $summary['activeQrCount'],
            'totalScans' => $summary['totalScans'],
            'filteredQrCount' => $filteredQrCount,
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

    private function applyFilters(Builder $query, bool $activeSelected, bool $scannedSelected): Builder
    {
        if ($activeSelected) {
            $query->where('is_active', true);
        }

        if ($scannedSelected) {
            $query->whereHas('scans');
        }

        return $query;
    }
}
