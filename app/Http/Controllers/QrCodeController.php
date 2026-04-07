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


        $payload['title'] = $this->titleCaseTr((string) $payload['title']);

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
            size: 440,
            margin: 18,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
            foregroundColor: new Color(13, 35, 46),
            backgroundColor: new Color(255, 255, 255, 0),
        ))->build();

        $qrSize = $qrResult->getMatrix()->getOuterSize();
        $qrContent = $this->extractSvgContents($qrResult->getString());

        $canvasWidth = 720;
        $canvasHeight = 1040;
        $centerX = $canvasWidth / 2;

        $brandLogoWidth = 260;
        $brandLogoHeight = 78;
        $brandLogoX = ($canvasWidth - $brandLogoWidth) / 2;
        $brandLogoY = 80;

        $qrPanelSize = 520;
        $qrPanelX = ($canvasWidth - $qrPanelSize) / 2;
        $qrPanelY = $brandLogoY + $brandLogoHeight + 50;

        $qrTranslateX = $qrPanelX + (($qrPanelSize - $qrSize) / 2);
        $qrTranslateY = $qrPanelY + (($qrPanelSize - $qrSize) / 2);

        $logoBadgeSize = 90;
        $logoBadgeX = ($canvasWidth - $logoBadgeSize) / 2;
        $logoBadgeY = $qrPanelY + (($qrPanelSize - $logoBadgeSize) / 2);
        $logoSize = 48;
        $logoX = ($canvasWidth - $logoSize) / 2;
        $logoY = $logoBadgeY + (($logoBadgeSize - $logoSize) / 2);

        $brandLogoDataUri = $this->imageDataUri(public_path('img/yee-logo.png'));
        $faviconDataUri = $this->imageDataUri(public_path('img/yee-favicon.png'));

        $rawDepartmentName = trim((string) ($qrCode->department?->name ?? 'Kurumsal Birim'));
        $rawDepartmentName = mb_strtoupper(str_replace(['i', 'ı'], ['İ', 'I'], $rawDepartmentName), 'UTF-8');

        $departmentLines = $this->svgWrappedLines($rawDepartmentName, 32, 2);
        $departmentLineCount = max(count($departmentLines), 1);
        $departmentFontSize = match (true) {
            $departmentLineCount >= 2 => 26,
            default => 30,
        };
        $departmentLineHeight = $departmentFontSize + 14;

        $textStartY = $qrPanelY + $qrPanelSize + 85;
        $departmentFirstY = $textStartY + $departmentFontSize;
        $departmentTspans = $this->svgTspans($departmentLines, $centerX, $departmentFirstY, $departmentLineHeight);

        $signatureTitle = $this->titleCaseTr((string) $qrCode->title);
        $titleFontSize = 22;
        $titleLineHeight = 28;
        $titleLines = $this->svgWrappedLines($signatureTitle, 45, 2);
        $titleLineCount = max(count($titleLines), 1);
        $titleStartY = $departmentFirstY + (($departmentLineCount - 1) * $departmentLineHeight) + 40;
        $titleTspans = $this->svgTspans($titleLines, $centerX, $titleStartY, $titleLineHeight);
        $titleBoxWidth = 560;
        $titleBoxX = ($canvasWidth - $titleBoxWidth) / 2;
        $titleBoxPaddingY = 12;
        $titleBoxHeight = ($titleLineCount * $titleLineHeight) + ($titleBoxPaddingY * 2);
        $titleBoxY = $titleStartY - $titleFontSize - $titleBoxPaddingY;

        $brandTextY = $brandLogoY + 45;
        $canvasInnerWidth = $canvasWidth - 2;
        $canvasInnerHeight = $canvasHeight - 2;

        return <<<SVG
<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" width="{$canvasWidth}" height="{$canvasHeight}" viewBox="0 0 {$canvasWidth} {$canvasHeight}" fill="none" role="img" aria-labelledby="qr-title qr-subtitle">
    <title id="qr-title">Yunus Emre Enstitüsü Kurumsal QR</title>
    <desc id="qr-subtitle">{$this->svgText($rawDepartmentName)} için markalı kurumsal QR kartı</desc>
    <defs>
        <linearGradient id="glassCard" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" stop-color="#FFFFFF" stop-opacity="1"/>
            <stop offset="100%" stop-color="#F2F8F9" stop-opacity="1"/>
        </linearGradient>
        <linearGradient id="glassStroke" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" stop-color="#FFFFFF" stop-opacity="0.9"/>
            <stop offset="100%" stop-color="#DEEBEF" stop-opacity="0.3"/>
        </linearGradient>

        <filter id="qrShadow" x="-5%" y="-5%" width="110%" height="110%" color-interpolation-filters="sRGB">
            <feGaussianBlur in="SourceAlpha" stdDeviation="15" result="blur"/>
            <feOffset dy="8" result="offsetBlur"/>
            <feComponentTransfer>
                <feFuncA type="linear" slope="0.04"/>
            </feComponentTransfer>
            <feMerge>
                <feMergeNode/>
                <feMergeNode in="SourceGraphic"/>
            </feMerge>
        </filter>
        
        <filter id="badgeShadow" x="-20%" y="-20%" width="140%" height="140%" color-interpolation-filters="sRGB">
            <feDropShadow dx="0" dy="6" stdDeviation="8" flood-color="#000000" flood-opacity="0.10"/>
        </filter>
    </defs>

    <rect width="{$canvasWidth}" height="{$canvasHeight}" rx="48" fill="url(#glassCard)"/>
    <rect x="1" y="1" width="{$canvasInnerWidth}" height="{$canvasInnerHeight}" rx="47" fill="none" stroke="url(#glassStroke)" stroke-width="2"/>
SVG
            . ($brandLogoDataUri !== ''
                ? "\n    <image href=\"{$brandLogoDataUri}\" x=\"{$brandLogoX}\" y=\"{$brandLogoY}\" width=\"{$brandLogoWidth}\" height=\"{$brandLogoHeight}\" preserveAspectRatio=\"xMidYMid meet\"/>"
                : "\n    <text x=\"{$centerX}\" y=\"{$brandTextY}\" text-anchor=\"middle\" font-family=\"-apple-system, system-ui, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif\" font-size=\"32\" font-weight=\"800\" letter-spacing=\"-0.5\" fill=\"#10202A\">Yunus Emre Enstitüsü</text>")
            . <<<SVG

    <g filter="url(#qrShadow)">
        <rect x="{$qrPanelX}" y="{$qrPanelY}" width="{$qrPanelSize}" height="{$qrPanelSize}" rx="32" fill="#FFFFFF"/>
    </g>
    <g transform="translate({$qrTranslateX} {$qrTranslateY})">
        {$qrContent}
    </g>
SVG
            . ($faviconDataUri !== ''
                ? <<<SVG

    <g filter="url(#badgeShadow)">
        <rect x="{$logoBadgeX}" y="{$logoBadgeY}" width="{$logoBadgeSize}" height="{$logoBadgeSize}" rx="24" fill="#FFFFFF"/>
        <image href="{$faviconDataUri}" x="{$logoX}" y="{$logoY}" width="{$logoSize}" height="{$logoSize}" preserveAspectRatio="xMidYMid meet"/>
    </g>
SVG
                : '')
            . <<<SVG

    <text x="{$centerX}" text-anchor="middle" font-family="-apple-system, system-ui, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif" font-size="{$departmentFontSize}" font-weight="700" letter-spacing="1.5" fill="#1D1D1F">{$departmentTspans}</text>
    
    <rect x="{$titleBoxX}" y="{$titleBoxY}" width="{$titleBoxWidth}" height="{$titleBoxHeight}" rx="20" fill="#F7FAFB" stroke="#E1EEF1" stroke-width="1.5"/>
    <text x="{$centerX}" text-anchor="middle" font-family="-apple-system, system-ui, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif" font-size="{$titleFontSize}" font-weight="600" letter-spacing="0.12em" fill="#1D1D1F">{$titleTspans}</text>
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

    private function titleCaseTr(string $value): string
    {
        $normalized = trim((string) preg_replace('/\s+/u', ' ', $value));

        if ($normalized === '') {
            return '';
        }

        $words = preg_split('/\s+/u', $normalized) ?: [];
        $titled = [];

        foreach ($words as $word) {
            $lower = $this->trLower($word);
            $first = mb_substr($lower, 0, 1, 'UTF-8');
            $rest = mb_substr($lower, 1, null, 'UTF-8');
            $titled[] = $this->trUpper($first) . $rest;
        }

        return implode(' ', $titled);
    }

    private function trLower(string $value): string
    {
        $value = str_replace(['İ', 'I'], ['i', 'ı'], $value);

        return mb_strtolower($value, 'UTF-8');
    }

    private function trUpper(string $value): string
    {
        $value = str_replace(['i', 'ı'], ['İ', 'I'], $value);

        return mb_strtoupper($value, 'UTF-8');
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
