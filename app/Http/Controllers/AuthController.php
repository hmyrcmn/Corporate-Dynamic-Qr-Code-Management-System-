<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\LdapDirectoryAuthenticator;
use App\Support\LdapUsername;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use LdapRecord\Auth\BindException;
use LdapRecord\ConnectionException;
use Throwable;

class AuthController extends Controller
{
    public function __construct(
        private readonly LdapDirectoryAuthenticator $ldapDirectoryAuthenticator,
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

        $normalizedUsername = LdapUsername::normalize($credentials['username']);
        $remember = $request->boolean('remember');
        $errorMessage = 'Kullanici adi veya sifre dogrulanamadi.';

        $authenticated = $this->attemptLocalSuperAdmin(
            $normalizedUsername,
            $credentials['password'],
            $remember,
        );

        if (! $authenticated) {
            [$authenticated, $errorMessage] = $this->attemptConfiguredAuthentication(
                $normalizedUsername,
                $credentials['password'],
                $remember,
            );
        }

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

    private function attemptLocalSuperAdmin(string $username, string $password, bool $remember): bool
    {
        if (! config('dynamicqr.local_super_admin_enabled')) {
            return false;
        }

        $user = User::query()
            ->where('username', $username)
            ->where('is_active', true)
            ->first();

        if (! $user || blank($user->password) || ! Hash::check($password, $user->password)) {
            return false;
        }

        Auth::login($user, $remember);

        return true;
    }

    /**
     * @return array{0: bool, 1: string}
     */
    private function attemptConfiguredAuthentication(string $username, string $password, bool $remember): array
    {
        if (config('dynamicqr.ldap_enabled')) {
            $loginAttribute = (string) config('dynamicqr.ldap_login_attribute', 'samaccountname');

            try {
                if (! $this->ldapDirectoryAuthenticator->shouldUseDirectUserBind()) {
                    $authenticated = Auth::attempt([
                        $loginAttribute => $username,
                        'password' => $password,
                    ], $remember);

                    if ($authenticated) {
                        return [true, 'Kullanici adi veya sifre dogrulanamadi.'];
                    }
                }

                $user = $this->ldapDirectoryAuthenticator->attempt($username, $password);

                if ($user) {
                    Auth::login($user, $remember);

                    return [true, 'Kullanici adi veya sifre dogrulanamadi.'];
                }

                return [
                    false,
                    $this->ldapDirectoryAuthenticator->lastErrorMessage() ?? 'Kullanici adi veya sifre dogrulanamadi.',
                ];
            } catch (BindException|ConnectionException $exception) {
                Log::warning('LDAP giris baglantisi basarisiz oldu.', [
                    'username' => $username,
                    'message' => $exception->getMessage(),
                ]);

                return [false, 'LDAP sunucusuna baglanilamadi. Sunucu ve bind ayarlarinizi kontrol edin.'];
            } catch (Throwable $exception) {
                Log::error('LDAP giris akisi beklenmeyen bir hatayla karsilasti.', [
                    'username' => $username,
                    'message' => $exception->getMessage(),
                ]);

                return [false, 'LDAP girisi sirasinda beklenmeyen bir hata olustu.'];
            }
        }

        return [
            Auth::attempt([
                'username' => $username,
                'password' => $password,
            ], $remember),
            'Kullanici adi veya sifre dogrulanamadi.',
        ];
    }

    private function rejectInvalidAuthenticatedUser(Request $request): ?RedirectResponse
    {
        $user = $request->user();

        if (! $user) {
            return null;
        }

        if (! $user->is_active) {
            return $this->rejectAuthenticatedSession($request, 'Hesap pasif durumda. Sistem yoneticisi ile iletisime gecin.');
        }

        if (! $user->hasGlobalAccess() && ! $user->department_id) {
            return $this->rejectAuthenticatedSession($request, 'LDAP kaydinizda birim bilgisi bulunamadi. Erisim icin department alani gerekli.');
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
