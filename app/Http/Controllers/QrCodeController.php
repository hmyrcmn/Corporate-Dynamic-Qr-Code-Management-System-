<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\QrCode;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class QrCodeController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        if ($request->user()->hasGlobalDepartmentAccess()) {
            return $this->redirectGlobalUserToDepartmentHub();
        }

        return view('qr.form', [
            'qrCode' => new QrCode(['is_active' => true]),
            'formAction' => route('qr.store'),
            'submitLabel' => 'QR Oluştur',
            'pageTitle' => 'Yeni Bağlantı Oluştur',
            'selectedDepartment' => $request->user()->department,
            'backUrl' => route('dashboard'),
            'downloadUrl' => null,
            'deleteConfirmUrl' => null,
        ]);
    }

    public function createForDepartment(Request $request, Department $department): View
    {
        abort_unless($request->user()->hasGlobalDepartmentAccess(), 404);

        return view('qr.form', [
            'qrCode' => new QrCode(['is_active' => true]),
            'formAction' => route('qr.department.store', $department),
            'submitLabel' => 'QR Oluştur',
            'pageTitle' => 'Yeni Bağlantı Oluştur',
            'selectedDepartment' => $department,
            'backUrl' => route('dashboard.department', $department),
            'downloadUrl' => null,
            'deleteConfirmUrl' => null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->hasGlobalDepartmentAccess()) {
            return $this->redirectGlobalUserToDepartmentHub();
        }

        abort_if(
            !$user->department_id,
            Response::HTTP_BAD_REQUEST,
            'QR oluşturmak için kullanıcının birimi tanımlı olmalı.',
        );

        $payload = $this->validatedPayload($request);

        QrCode::create([
            'title' => $payload['title'],
            'destination_url' => $payload['destination_url'],
            'is_active' => (bool) ($payload['is_active'] ?? false),
            'department_id' => $user->department_id,
            'created_by_id' => $user->id,
        ]);

        return redirect()->route('dashboard')->with('status', 'QR kaydı oluşturuldu.');
    }

    public function storeForDepartment(Request $request, Department $department): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user->hasGlobalDepartmentAccess(), 404);

        $payload = $this->validatedPayload($request);

        QrCode::create([
            'title' => $payload['title'],
            'destination_url' => $payload['destination_url'],
            'is_active' => (bool) ($payload['is_active'] ?? false),
            'department_id' => $department->id,
            'created_by_id' => $user->id,
        ]);

        return redirect()
            ->route('dashboard.department', $department)
            ->with('status', 'QR kaydı oluşturuldu.');
    }

    public function edit(Request $request, string $shortId): View|RedirectResponse
    {
        if ($request->user()->hasGlobalDepartmentAccess()) {
            return $this->redirectGlobalUserToDepartmentHub();
        }

        $qrCode = $this->resolveAccessibleQrCode($request, $shortId);

        return view('qr.form', [
            'qrCode' => $qrCode,
            'formAction' => route('qr.update', $qrCode->short_id),
            'submitLabel' => 'Kaydet',
            'pageTitle' => 'Bağlantıyı Düzenle',
            'selectedDepartment' => $qrCode->department,
            'backUrl' => route('dashboard'),
            'downloadUrl' => route('qr.download', $qrCode->short_id),
            'deleteConfirmUrl' => route('qr.delete.confirm', $qrCode->short_id),
        ]);
    }

    public function editForDepartment(Request $request, Department $department, string $shortId): View
    {
        abort_unless($request->user()->hasGlobalDepartmentAccess(), 404);

        $qrCode = $this->resolveDepartmentScopedQrCode($request, $department, $shortId);

        return view('qr.form', [
            'qrCode' => $qrCode,
            'formAction' => route('qr.department.update', [
                'department' => $department,
                'shortId' => $qrCode->short_id,
            ]),
            'submitLabel' => 'Kaydet',
            'pageTitle' => 'Bağlantıyı Düzenle',
            'selectedDepartment' => $department,
            'backUrl' => route('dashboard.department', $department),
            'downloadUrl' => route('qr.department.download', [
                'department' => $department,
                'shortId' => $qrCode->short_id,
            ]),
            'deleteConfirmUrl' => route('qr.department.delete.confirm', [
                'department' => $department,
                'shortId' => $qrCode->short_id,
            ]),
        ]);
    }

    public function update(Request $request, string $shortId): RedirectResponse
    {
        if ($request->user()->hasGlobalDepartmentAccess()) {
            return $this->redirectGlobalUserToDepartmentHub();
        }

        $qrCode = $this->resolveAccessibleQrCode($request, $shortId);
        $payload = $this->validatedPayload($request);

        $qrCode->update([
            'title' => $payload['title'],
            'destination_url' => $payload['destination_url'],
            'is_active' => (bool) ($payload['is_active'] ?? false),
        ]);

        return redirect()->route('dashboard')->with('status', 'QR kaydı güncellendi.');
    }

    public function updateForDepartment(Request $request, Department $department, string $shortId): RedirectResponse
    {
        abort_unless($request->user()->hasGlobalDepartmentAccess(), 404);

        $qrCode = $this->resolveDepartmentScopedQrCode($request, $department, $shortId);
        $payload = $this->validatedPayload($request);

        $qrCode->update([
            'title' => $payload['title'],
            'destination_url' => $payload['destination_url'],
            'is_active' => (bool) ($payload['is_active'] ?? false),
        ]);

        return redirect()
            ->route('dashboard.department', $department)
            ->with('status', 'QR kaydı güncellendi.');
    }

    public function confirmDelete(Request $request, string $shortId): View|RedirectResponse
    {
        if ($request->user()->hasGlobalDepartmentAccess()) {
            return $this->redirectGlobalUserToDepartmentHub();
        }

        $qrCode = $this->resolveAccessibleQrCode($request, $shortId);

        return view('qr.delete', [
            'qrCode' => $qrCode,
            'deleteAction' => route('qr.delete', $qrCode->short_id),
            'backUrl' => route('dashboard'),
        ]);
    }

    public function confirmDeleteForDepartment(Request $request, Department $department, string $shortId): View
    {
        abort_unless($request->user()->hasGlobalDepartmentAccess(), 404);

        $qrCode = $this->resolveDepartmentScopedQrCode($request, $department, $shortId);

        return view('qr.delete', [
            'qrCode' => $qrCode,
            'deleteAction' => route('qr.department.delete', [
                'department' => $department,
                'shortId' => $qrCode->short_id,
            ]),
            'backUrl' => route('dashboard.department', $department),
        ]);
    }

    public function destroy(Request $request, string $shortId): RedirectResponse
    {
        if ($request->user()->hasGlobalDepartmentAccess()) {
            return $this->redirectGlobalUserToDepartmentHub();
        }

        $this->resolveAccessibleQrCode($request, $shortId)->delete();

        return redirect()->route('dashboard')->with('status', 'QR kaydı silindi.');
    }

    public function destroyForDepartment(Request $request, Department $department, string $shortId): RedirectResponse
    {
        abort_unless($request->user()->hasGlobalDepartmentAccess(), 404);

        $this->resolveDepartmentScopedQrCode($request, $department, $shortId)->delete();

        return redirect()
            ->route('dashboard.department', $department)
            ->with('status', 'QR kaydı silindi.');
    }

    public function download(Request $request, string $shortId): Response
    {
        if ($request->user()->hasGlobalDepartmentAccess()) {
            return $this->redirectGlobalUserToDepartmentHub();
        }

        return $this->downloadQrCodeResponse(
            $this->resolveAccessibleQrCode($request, $shortId),
            $request->boolean('inline'),
        );
    }

    public function downloadForDepartment(Request $request, Department $department, string $shortId): Response
    {
        abort_unless($request->user()->hasGlobalDepartmentAccess(), 404);

        return $this->downloadQrCodeResponse(
            $this->resolveDepartmentScopedQrCode($request, $department, $shortId),
            $request->boolean('inline'),
        );
    }

    private function resolveAccessibleQrCode(Request $request, string $shortId): QrCode
    {
        return QrCode::query()
            ->with(['department', 'creator'])
            ->withCount('scans')
            ->accessibleTo($request->user())
            ->where('short_id', $shortId)
            ->firstOrFail();
    }

    private function resolveDepartmentScopedQrCode(Request $request, Department $department, string $shortId): QrCode
    {
        return QrCode::query()
            ->with(['department', 'creator'])
            ->withCount('scans')
            ->accessibleTo($request->user())
            ->where('department_id', $department->id)
            ->where('short_id', $shortId)
            ->firstOrFail();
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedPayload(Request $request): array
    {
        $payload = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'destination_url' => ['required', 'url', 'max:2048'],
            'is_active' => ['nullable', 'boolean'],
            'department_id' => ['prohibited'],
        ]);

        $host = (string) parse_url($payload['destination_url'], PHP_URL_HOST);
        $allowedDomains = collect(config('dynamicqr.allowed_qr_domains'));
        $isAllowed = $allowedDomains->contains(
            fn(string $domain): bool => $host === $domain || str_ends_with($host, '.' . $domain),
        );

        if (!$isAllowed) {
            throw ValidationException::withMessages([
                'destination_url' => 'Yalnızca izinli kurumsal alan adlarına yönlendirme yapabilirsiniz.',
            ]);
        }

        return $payload;
    }

    private function redirectGlobalUserToDepartmentHub(): RedirectResponse
    {
        return redirect()
            ->route('dashboard')
            ->with('status', 'QR işlemleri için önce birim seçin.');
    }

    private function downloadQrCodeResponse(QrCode $qrCode, bool $inline): Response
    {
        $payload = $this->buildBrandedQrSvg($qrCode);
        $disposition = $inline ? 'inline' : 'attachment';

        return response($payload, 200, [
            'Content-Type' => 'image/svg+xml',
            'Content-Disposition' => sprintf('%s; filename="qr-%s.svg"', $disposition, $qrCode->short_id),
        ]);
    }

    private function buildBrandedQrSvg(QrCode $qrCode): string
    {
        $qrResult = (new Builder(
            writer: new SvgWriter(),
            data: route('qr.redirect', $qrCode->short_id),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: 420,
            margin: 18,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
            foregroundColor: new Color(16, 32, 42),
            backgroundColor: new Color(255, 255, 255),
        ))->build();

        $qrSize = $qrResult->getMatrix()->getOuterSize();
        $qrContent = $this->extractSvgContents($qrResult->getString());

        $canvasWidth = 760;
        $canvasHeight = 940;
        $qrPanelSize = 500;
        $qrPanelX = 130;
        $qrPanelY = 156;
        $qrTranslateX = $qrPanelX + (($qrPanelSize - $qrSize) / 2);
        $qrTranslateY = $qrPanelY + (($qrPanelSize - $qrSize) / 2);
        $logoBadgeSize = 78;
        $logoBadgeX = ($canvasWidth - $logoBadgeSize) / 2;
        $logoBadgeY = $qrPanelY + (($qrPanelSize - $logoBadgeSize) / 2);
        $logoSize = 36;
        $logoX = ($canvasWidth - $logoSize) / 2;
        $logoY = $logoBadgeY + (($logoBadgeSize - $logoSize) / 2);
        $brandLogoWidth = 214;
        $brandLogoHeight = 64;
        $brandLogoX = ($canvasWidth - $brandLogoWidth) / 2;
        $brandLogoY = 72;

        $brandLogoDataUri = $this->imageDataUri(public_path('img/yee-logo.png'));
        $faviconDataUri = $this->imageDataUri(public_path('img/yee-favicon.png'));

        $rawDepartmentName = trim((string) ($qrCode->department?->name ?? 'Kurumsal Birim'));
        $departmentLines = $this->svgWrappedLines($rawDepartmentName, 24, 4);
        $departmentLineCount = max(count($departmentLines), 1);
        $departmentFontSize = match (true) {
            $departmentLineCount >= 4 => 17,
            $departmentLineCount === 3 => 19,
            $departmentLineCount === 2 => 21,
            default => 24,
        };
        $departmentLineHeight = $departmentFontSize + 4;
        $departmentFirstY = 754;
        $departmentTspans = $this->svgTspans($departmentLines, 380, $departmentFirstY, $departmentLineHeight);
        $titleLines = $this->svgWrappedLines((string) $qrCode->title, 30, 3);
        $titleFirstY = $departmentFirstY + (($departmentLineCount - 1) * $departmentLineHeight) + 28;
        $titleTspans = $this->svgTspans($titleLines, 380, $titleFirstY, 18);

        return <<<SVG
<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" width="{$canvasWidth}" height="{$canvasHeight}" viewBox="0 0 {$canvasWidth} {$canvasHeight}" fill="none" role="img" aria-labelledby="qr-title qr-subtitle">
    <title id="qr-title">Yunus Emre Enstitüsü Kurumsal QR</title>
    <desc id="qr-subtitle">{$this->svgText($rawDepartmentName)} için markalı kurumsal QR kartı</desc>
    <defs>
        <linearGradient id="surfaceGradient" x1="48" y1="24" x2="712" y2="916" gradientUnits="userSpaceOnUse">
            <stop stop-color="#F6F9F9"/>
            <stop offset="1" stop-color="#EDF3F4"/>
        </linearGradient>
        <linearGradient id="cardGradient" x1="68" y1="34" x2="692" y2="906" gradientUnits="userSpaceOnUse">
            <stop stop-color="#FFFFFF"/>
            <stop offset="1" stop-color="#F9FCFC"/>
        </linearGradient>
        <linearGradient id="cardStroke" x1="68" y1="32" x2="692" y2="906" gradientUnits="userSpaceOnUse">
            <stop stop-color="#EAF1F2"/>
            <stop offset="1" stop-color="#DFEAEC"/>
        </linearGradient>
        <linearGradient id="infoGradient" x1="112" y1="690" x2="648" y2="858" gradientUnits="userSpaceOnUse">
            <stop stop-color="#FAFCFC"/>
            <stop offset="1" stop-color="#F4F8F9"/>
        </linearGradient>
        <linearGradient id="badgeGradient" x1="320" y1="372" x2="408" y2="460" gradientUnits="userSpaceOnUse">
            <stop stop-color="#FFFFFF"/>
            <stop offset="1" stop-color="#F5F9FA"/>
        </linearGradient>
        <filter id="cardShadow" x="20" y="12" width="720" height="916" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
            <feFlood flood-opacity="0" result="BackgroundImageFix"/>
            <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/>
            <feOffset dy="18"/>
            <feGaussianBlur stdDeviation="22"/>
            <feComposite in2="hardAlpha" operator="out"/>
            <feColorMatrix type="matrix" values="0 0 0 0 0.0509804 0 0 0 0 0.105882 0 0 0 0 0.129412 0 0 0 0.11 0"/>
            <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_0_1"/>
            <feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow_0_1" result="shape"/>
        </filter>
        <filter id="panelShadow" x="118" y="144" width="524" height="524" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
            <feFlood flood-opacity="0" result="BackgroundImageFix"/>
            <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/>
            <feOffset dy="8"/>
            <feGaussianBlur stdDeviation="10"/>
            <feComposite in2="hardAlpha" operator="out"/>
            <feColorMatrix type="matrix" values="0 0 0 0 0.0705882 0 0 0 0 0.239216 0 0 0 0 0.27451 0 0 0 0.05 0"/>
            <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_0_2"/>
            <feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow_0_2" result="shape"/>
        </filter>
        <filter id="badgeShadow" x="300" y="362" width="122" height="122" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
            <feFlood flood-opacity="0" result="BackgroundImageFix"/>
            <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/>
            <feOffset dy="6"/>
            <feGaussianBlur stdDeviation="7"/>
            <feComposite in2="hardAlpha" operator="out"/>
            <feColorMatrix type="matrix" values="0 0 0 0 0.0705882 0 0 0 0 0.239216 0 0 0 0 0.27451 0 0 0 0.12 0"/>
            <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_0_3"/>
            <feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow_0_3" result="shape"/>
        </filter>
    </defs>
    <rect width="{$canvasWidth}" height="{$canvasHeight}" fill="url(#surfaceGradient)"/>
    <g filter="url(#cardShadow)">
        <rect x="44" y="28" width="672" height="884" rx="48" fill="url(#cardGradient)"/>
        <rect x="44.75" y="28.75" width="670.5" height="882.5" rx="47.25" stroke="url(#cardStroke)" stroke-width="1"/>
    </g>
SVG
            . ($brandLogoDataUri !== ''
                ? "\n    <image href=\"{$brandLogoDataUri}\" x=\"{$brandLogoX}\" y=\"{$brandLogoY}\" width=\"{$brandLogoWidth}\" height=\"{$brandLogoHeight}\" preserveAspectRatio=\"xMidYMid meet\"/>"
                : "\n    <text x=\"380\" y=\"112\" text-anchor=\"middle\" font-family=\"Inter, Arial, sans-serif\" font-size=\"28\" font-weight=\"700\" fill=\"#10202A\">Yunus Emre Enstitüsü</text>")
            . <<<SVG

    <g filter="url(#panelShadow)">
        <rect x="{$qrPanelX}" y="{$qrPanelY}" width="{$qrPanelSize}" height="{$qrPanelSize}" rx="38" fill="#FFFFFF"/>
    </g>
    <g transform="translate({$qrTranslateX} {$qrTranslateY})">
        {$qrContent}
    </g>
SVG
            . ($faviconDataUri !== ''
                ? <<<SVG

    <g filter="url(#badgeShadow)">
        <rect x="{$logoBadgeX}" y="{$logoBadgeY}" width="{$logoBadgeSize}" height="{$logoBadgeSize}" rx="24" fill="url(#badgeGradient)"/>
        <image href="{$faviconDataUri}" x="{$logoX}" y="{$logoY}" width="{$logoSize}" height="{$logoSize}" preserveAspectRatio="xMidYMid meet"/>
    </g>
SVG
                : '')
            . <<<SVG

    <g>
        <rect x="112" y="690" width="536" height="166" rx="30" fill="url(#infoGradient)"/>
        <text x="380" text-anchor="middle" font-family="Inter, 'Segoe UI', Arial, sans-serif" font-size="{$departmentFontSize}" font-weight="700" fill="#10202A">{$departmentTspans}</text>
        <text x="380" text-anchor="middle" font-family="Inter, Arial, sans-serif" font-size="15" font-weight="600" fill="#5A747A">{$titleTspans}</text>
    </g>
</svg>
SVG;
    }

    private function extractSvgContents(string $svg): string
    {
        $svg = preg_replace('/<\?xml.*?\?>\s*/', '', $svg) ?? $svg;

        if (preg_match('/<svg[^>]*>(.*)<\/svg>/s', $svg, $matches) !== 1) {
            return $svg;
        }

        return trim($matches[1]);
    }

    private function imageDataUri(string $path): string
    {
        if (!is_file($path)) {
            return '';
        }

        $contents = file_get_contents($path);

        if ($contents === false) {
            return '';
        }

        return 'data:image/png;base64,' . base64_encode($contents);
    }

    private function svgText(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }

    /**
     * @return array<int, string>
     */
    private function svgWrappedLines(string $value, int $lineLength, int $maxLines): array
    {
        $normalized = trim((string) preg_replace('/\s+/u', ' ', $value));

        if ($normalized === '') {
            return [];
        }

        $words = preg_split('/\s+/u', $normalized) ?: [];
        $lines = [];
        $currentLine = '';
        $remainingWords = false;

        foreach ($words as $index => $word) {
            $candidate = $currentLine === '' ? $word : $currentLine . ' ' . $word;

            if (Str::length($candidate) <= $lineLength) {
                $currentLine = $candidate;
                continue;
            }

            if ($currentLine === '') {
                $lines[] = Str::limit($word, $lineLength, '…');
            } else {
                $lines[] = $currentLine;
                $currentLine = $word;
            }

            if (count($lines) >= $maxLines) {
                $remainingWords = $index < count($words) - 1 || $currentLine !== '';
                break;
            }
        }

        if (!$remainingWords && $currentLine !== '' && count($lines) < $maxLines) {
            $lines[] = $currentLine;
        } elseif ($currentLine !== '' && count($lines) >= $maxLines) {
            $remainingWords = true;
        }

        $lines = array_slice($lines, 0, $maxLines);

        if ($remainingWords && $lines !== []) {
            $lastIndex = array_key_last($lines);
            $lines[$lastIndex] = Str::limit($lines[$lastIndex], $lineLength - 1, '…');
        }

        return array_map(fn(string $line): string => $this->svgText($line), $lines);
    }

    /**
     * @param  array<int, string>  $lines
     */
    private function svgTspans(array $lines, int $x, int $firstY, int $lineHeight): string
    {
        $tspans = [];

        foreach (array_values($lines) as $index => $line) {
            $y = $firstY + ($index * $lineHeight);
            $tspans[] = sprintf('<tspan x="%d" y="%d">%s</tspan>', $x, $y, $line);
        }

        return implode('', $tspans);
    }
}
