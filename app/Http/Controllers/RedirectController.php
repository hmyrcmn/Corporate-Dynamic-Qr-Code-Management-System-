<?php

namespace App\Http\Controllers;

use App\Models\QrCode;
use App\Models\ScanAnalytics;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectController extends Controller
{
    public function __invoke(Request $request, string $shortId): RedirectResponse|Response
    {
        $qrCode = QrCode::query()
            ->where('short_id', $shortId)
            ->where('is_active', true)
            ->firstOrFail();

        $destinationHost = (string) parse_url($qrCode->destination_url, PHP_URL_HOST);

        if ($destinationHost !== '' && $destinationHost === $request->getHost()) {
            abort(Response::HTTP_BAD_REQUEST, 'Recursive redirection detected.');
        }

        $clientIp = trim(explode(',', (string) ($request->header('X-Forwarded-For') ?? $request->ip()))[0]);

        ScanAnalytics::create([
            'qr_code_id' => $qrCode->id,
            'timestamp' => now(),
            'ip_address_hash' => ScanAnalytics::hashIp($clientIp),
            'user_agent' => substr((string) $request->userAgent(), 0, 512),
        ]);

        return redirect()->away($qrCode->destination_url);
    }
}
