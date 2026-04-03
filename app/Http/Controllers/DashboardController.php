<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\QrCode;
use App\Models\ScanAnalytics;
use App\Models\User;
use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index(Request $request): ViewContract
    {
        $user = $request->user();

        if ($user->hasGlobalDepartmentAccess()) {
            return $this->renderDepartmentHub();
        }

        return $this->renderDepartmentDashboard($request, $user, $user->department);
    }

    public function showDepartment(Request $request, Department $department): ViewContract
    {
        $user = $request->user();

        abort_unless($user->hasGlobalDepartmentAccess(), 404);

        return $this->renderDepartmentDashboard($request, $user, $department);
    }

    private function renderDepartmentHub(): ViewContract
    {
        $scanCounts = ScanAnalytics::query()
            ->join('qr_codes', 'qr_codes.id', '=', 'scan_analytics.qr_code_id')
            ->selectRaw('qr_codes.department_id, COUNT(scan_analytics.id) as scans_count')
            ->groupBy('qr_codes.department_id')
            ->pluck('scans_count', 'qr_codes.department_id');

        $departments = Department::query()
            ->withCount([
                'qrCodes',
                'qrCodes as active_qr_codes_count' => fn (Builder $query) => $query->where('is_active', true),
            ])
            ->orderBy('name')
            ->get()
            ->map(function (Department $department) use ($scanCounts): Department {
                $department->setAttribute('scans_count', (int) ($scanCounts[$department->id] ?? 0));

                return $department;
            });

        return view('dashboard.departments', [
            'departments' => $departments,
        ]);
    }

    private function renderDepartmentDashboard(Request $request, User $user, ?Department $department): ViewContract
    {
        abort_if(! $department, 404);

        $activeSelected = $request->boolean('active');
        $scannedSelected = $request->boolean('scanned');

        $summary = Cache::remember(
            sprintf('dashboard-summary:%s:%s', $user->getAuthIdentifier(), $department->id),
            now()->addSeconds(30),
            function () use ($user, $department): array {
                $accessibleQrCodes = $this->scopedQrCodesQuery($user, $department);

                $totalScans = ScanAnalytics::query()
                    ->whereIn('qr_code_id', (clone $accessibleQrCodes)->select('qr_codes.id'))
                    ->count();

                return [
                    'allQrCount' => (clone $accessibleQrCodes)->count(),
                    'activeQrCount' => (clone $accessibleQrCodes)->where('is_active', true)->count(),
                    'totalScans' => $totalScans,
                ];
            },
        );

        $qrCodesQuery = $this->applyFilters(
            $this->scopedQrCodesQuery($user, $department)
                ->with([
                    'department:id,name',
                    'creator:id,name,username',
                ])
                ->withCount('scans'),
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
            $filterTitle = 'Aktif ve taranan kayıtlar';
            $filterDescription = 'Yalnızca aktif olan ve en az bir kez taranan bağlantılar listeleniyor.';
        } elseif ($activeSelected) {
            $filterTitle = 'Aktif kayıtlar';
            $filterDescription = 'Sadece yayında kalan bağlantılar görünüyor.';
        } elseif ($scannedSelected) {
            $filterTitle = 'Taranan kayıtlar';
            $filterDescription = 'En az bir kez taranan bağlantılar öne çıkıyor.';
        } else {
            $filterTitle = 'Tüm kayıtlar';
            $filterDescription = 'Tüm bağlantılar, hedef adresler ve işlemler tek alanda listelenir.';
        }

        $dashboardRoute = $user->hasGlobalDepartmentAccess()
            ? route('dashboard.department', $department)
            : route('dashboard');

        return view('dashboard.index', [
            'selectedDepartment' => $department,
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
            'activeFilterUrl' => $this->filterUrl($dashboardRoute, [
                'active' => ! $activeSelected,
                'scanned' => $scannedSelected,
            ]),
            'scannedFilterUrl' => $this->filterUrl($dashboardRoute, [
                'active' => $activeSelected,
                'scanned' => ! $scannedSelected,
            ]),
            'resetFilterUrl' => $this->filterUrl($dashboardRoute, []),
            'departmentName' => $department->name,
            'createUrl' => $user->hasGlobalDepartmentAccess()
                ? route('qr.department.create', $department)
                : route('qr.create'),
            'departmentHubUrl' => $user->hasGlobalDepartmentAccess()
                ? route('dashboard')
                : null,
            'globalDepartmentMode' => $user->hasGlobalDepartmentAccess(),
        ]);
    }

    /**
     * @param  array<string, bool>  $filters
     */
    private function filterUrl(string $dashboardRoute, array $filters): string
    {
        $query = collect($filters)
            ->filter()
            ->map(fn (bool $value): string => $value ? '1' : '0')
            ->all();

        if ($query === []) {
            return $dashboardRoute;
        }

        return $dashboardRoute.'?'.http_build_query($query);
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

    private function scopedQrCodesQuery(User $user, Department $department): Builder
    {
        return QrCode::query()
            ->accessibleTo($user)
            ->where('department_id', $department->id);
    }
}
