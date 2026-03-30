import sys
import re

c_path = r"c:\Users\humeyra.cimen\Desktop\yunusEmre\laraveldynamicqr\app\Http\Controllers\QrCodeController.php"

with open(c_path, "r", encoding="utf-8") as f:
    content = f.read()

content_new = """<?php

namespace App\Http\Controllers;

use App\Models\QrCode;
use App\Models\Department;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class QrCodeController extends Controller
{
    public function create(Request $request): View
    {
        return view('qr.form', [
            'qrCode' => new QrCode(['is_active' => true]),
            'departments' => $request->user()->hasGlobalAccess() ? Department::where('is_active', true)->orderBy('name')->get() : collect(),
            'formAction' => route('qr.store'),
            'submitLabel' => 'QR Olustur',
            'pageTitle' => 'Yeni Baglanti Olustur',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        $payload = $this->validatedPayload($request);
        
        $departmentId = $user->hasGlobalAccess() ? $payload['department_id'] : $user->department_id;

        if (! $departmentId) {
            abort(Response::HTTP_BAD_REQUEST, 'QR olusturmak icin kullanicinin birimi tanimli olmali veya bir birim secilmelidir.');
        }

        QrCode::create([
            'title' => $payload['title'],
            'destination_url' => $payload['destination_url'],
            'is_active' => (bool) ($payload['is_active'] ?? false),
            'department_id' => $departmentId,
            'created_by_id' => $user->id,
        ]);

        return redirect()->route('dashboard')->with('status', 'QR kaydi olusturuldu.');
    }

    public function edit(Request $request, string $shortId): View
    {
        $qrCode = $this->resolveAccessibleQrCode($request, $shortId);

        return view('qr.form', [
            'qrCode' => $qrCode,
            'departments' => $request->user()->hasGlobalAccess() ? Department::where('is_active', true)->orderBy('name')->get() : collect(),
            'formAction' => route('qr.update', $qrCode->short_id),
            'submitLabel' => 'Kaydet',
            'pageTitle' => 'Baglantiyi Duzenle',
        ]);
    }

    public function update(Request $request, string $shortId): RedirectResponse
    {
        $qrCode = $this->resolveAccessibleQrCode($request, $shortId);
        $payload = $this->validatedPayload($request);
        
        $updateData = [
            'title' => $payload['title'],
            'destination_url' => $payload['destination_url'],
            'is_active' => (bool) ($payload['is_active'] ?? false),
        ];
        
        if ($request->user()->hasGlobalAccess()) {
            $updateData['department_id'] = $payload['department_id'];
        }

        $qrCode->update($updateData);

        return redirect()->route('dashboard')->with('status', 'QR kaydi guncellendi.');
    }

    public function confirmDelete(Request $request, string $shortId): View
    {
        return view('qr.delete', [
            'qrCode' => $this->resolveAccessibleQrCode($request, $shortId),
        ]);
    }

    public function destroy(Request $request, string $shortId): RedirectResponse
    {
        $this->resolveAccessibleQrCode($request, $shortId)->delete();

        return redirect()->route('dashboard')->with('status', 'QR kaydi silindi.');
    }

    public function download(Request $request, string $shortId): Response
    {
        $qrCode = $this->resolveAccessibleQrCode($request, $shortId);
        $payload = Builder::create()
            ->writer(new PngWriter())
            ->data(route('qr.redirect', $qrCode->short_id))
            ->size(480)
            ->margin(18)
            ->build();

        $disposition = $request->boolean('inline') ? 'inline' : 'attachment';

        return response($payload->getString(), 200, [
            'Content-Type' => 'image/png',
            'Content-Disposition' => sprintf('%s; filename="qr-%s.png"', $disposition, $qrCode->short_id),
        ]);
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

    /**
     * @return array<string, mixed>
     */
    private function validatedPayload(Request $request): array
    {
        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'destination_url' => ['required', 'url', 'max:2048'],
            'is_active' => ['nullable', 'boolean'],
        ];
        
        if ($request->user()->hasGlobalAccess()) {
            $rules['department_id'] = ['required', 'numeric', 'exists:departments,id'];
        }
        
        $payload = $request->validate($rules);

        $host = (string) parse_url($payload['destination_url'], PHP_URL_HOST);
        $allowedDomains = collect(config('dynamicqr.allowed_qr_domains'));
        $isAllowed = $allowedDomains->contains(
            fn (string $domain): bool => $host === $domain || str_ends_with($host, '.'.$domain),
        );

        if (! $isAllowed) {
            throw ValidationException::withMessages([
                'destination_url' => 'Yalnizca izinli kurumsal alan adlarina yonlendirme yapabilirsiniz.',
            ]);
        }

        return $payload;
    }
}
"""

with open(c_path, "w", encoding="utf-8") as f:
    f.write(content_new)

print("Controller updated")
