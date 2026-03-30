import sys

# 1. Update DashboardController.php for Performance
c_path = r"c:\Users\humeyra.cimen\Desktop\yunusEmre\laraveldynamicqr\app\Http\Controllers\DashboardController.php"
content_new = """<?php

namespace App\Http\Controllers;

use App\Models\QrCode;
use App\Models\ScanAnalytics;
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

        // Performance Optimization: Prevent pulling N records into memory
        $allQrCount = (clone $baseQuery)->count();
        $activeQrCount = (clone $baseQuery)->where('is_active', true)->count();
        
        $qrIdsQuery = (clone $baseQuery)->select('id');
        $totalScans = ScanAnalytics::whereIn('qr_code_id', $qrIdsQuery)->count();

        $activeSelected = $request->boolean('active');
        $scannedSelected = $request->boolean('scanned');

        $qrCodesQuery = (clone $baseQuery);

        if ($activeSelected) {
            $qrCodesQuery->where('is_active', true);
        }

        if ($scannedSelected) {
            $qrCodesQuery->whereHas('scans');
        }

        $filteredQrCount = $qrCodesQuery->count();

        // Paginate to prevent massive DOM overhead
        $qrCodes = $qrCodesQuery
            ->orderByDesc($scannedSelected ? 'scans_count' : 'created_at')
            ->orderByDesc('created_at')
            ->paginate(30)
            ->withQueryString();

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
            'allQrCount' => $allQrCount,
            'activeQrCount' => $activeQrCount,
            'totalScans' => $totalScans,
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
}
"""
with open(c_path, "w", encoding="utf-8") as f:
    f.write(content_new)


# 2. Update QrCodeController.php for SVG QR Generation (No GD required)
q_path = r"c:\Users\humeyra.cimen\Desktop\yunusEmre\laraveldynamicqr\app\Http\Controllers\QrCodeController.php"
with open(q_path, "r", encoding="utf-8") as f:
    q_content = f.read()

# Replace PngWriter with SvgWriter
q_content = q_content.replace('use Endroid\\QrCode\\Writer\\PngWriter;', 'use Endroid\\QrCode\\Writer\\SvgWriter;')
q_content = q_content.replace('new PngWriter()', 'new SvgWriter()')
q_content = q_content.replace("image/png", "image/svg+xml")
q_content = q_content.replace(".png", ".svg")
# Fix margin size bug for Endroid depending on version
q_content = q_content.replace("->margin(18)", "->margin(2)") 

with open(q_path, "w", encoding="utf-8") as f:
    f.write(q_content)


# 3. Update dashboard/index.blade.php for Error Handling and Pagination
d_path = r"c:\Users\humeyra.cimen\Desktop\yunusEmre\laraveldynamicqr\resources\views\dashboard\index.blade.php"
with open(d_path, "r", encoding="utf-8") as f:
    d_content = f.read()

pagination_html = r"""                    @endforelse
                </div>
                
                @if($qrCodes->hasPages())
                <div class="mt-6 flex justify-center pb-2">
                    {{ $qrCodes->links() }}
                </div>
                @endif
            </div>"""
d_content = d_content.replace("""                    @endforelse
                </div>
            </div>""", pagination_html)

js_new = r"""            imgEl.onload = function () {
                loader.classList.add('hidden');
                imgEl.classList.remove('hidden');
            };
            imgEl.onerror = function () {
                loader.classList.add('hidden');
                imgEl.classList.add('hidden');
                titleEl.textContent = 'Gorsel Yuklenemedi (Tarayici veya Sunucu Hatasi)';
                titleEl.classList.add('text-rose-500');
            };"""
d_content = d_content.replace("""            imgEl.onload = function () {
                loader.classList.add('hidden');
                imgEl.classList.remove('hidden');
            };""", js_new)

with open(d_path, "w", encoding="utf-8") as f:
    f.write(d_content)

print("Done updates")
