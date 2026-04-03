<?php

namespace App\Http\Controllers;

use App\Services\CredentialsAuthenticator;
use App\Support\LdapUsername;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(
        private readonly CredentialsAuthenticator $credentialsAuthenticator,
    ) {}

    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
        ]);

        $remember = $request->boolean('remember');
        [$authenticated, $errorMessage] = $this->credentialsAuthenticator->attempt(
            LdapUsername::normalize($credentials['username']),
            $credentials['password'],
            $remember,
        );

        if (! $authenticated) {
            return back()
                ->withErrors(['username' => $errorMessage])
                ->onlyInput('username');
        }

        if ($invalidUserResponse = $this->rejectInvalidAuthenticatedUser($request)) {
            return $invalidUserResponse;
        }

        $request->session()->regenerate();
        $request->user()?->forceFill(['last_login_at' => now()])->saveQuietly();

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('landing');
    }
    private function rejectInvalidAuthenticatedUser(Request $request): ?RedirectResponse
    {
        $user = $request->user();

        if (! $user) {
            return null;
        }

        if (! $user->is_active) {
            return $this->rejectAuthenticatedSession($request, 'Hesap pasif durumda. Sistem yöneticisi ile iletişime geçin.');
        }

        if (! $user->hasGlobalDepartmentAccess() && ! $user->department_id) {
            return $this->rejectAuthenticatedSession($request, 'LDAP kaydınızda birim bilgisi bulunamadı. Erişim için department alanı gerekli.');
        }

        return null;
    }

    private function rejectAuthenticatedSession(Request $request, string $message): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->withErrors(['username' => $message])
            ->onlyInput('username');
    }
}
