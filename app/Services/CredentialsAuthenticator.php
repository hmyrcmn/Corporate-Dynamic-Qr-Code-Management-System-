<?php

namespace App\Services;

use App\Models\User;
use App\Support\LdapUsername;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use LdapRecord\Auth\BindException;
use LdapRecord\ConnectionException;
use Throwable;

class CredentialsAuthenticator
{
    public function __construct(
        private readonly LdapDirectoryAuthenticator $ldapDirectoryAuthenticator,
    ) {}

    /**
     * @return array{0: bool, 1: string, 2: ?User}
     */
    public function attempt(string $username, string $password, bool $remember = false): array
    {
        $normalizedUsername = LdapUsername::normalize($username);
        $errorMessage = 'Kullanici adi veya sifre dogrulanamadi.';

        if ($this->attemptLocalAccount($normalizedUsername, $password, $remember)) {
            /** @var User|null $user */
            $user = Auth::user();

            return [true, $errorMessage, $user];
        }

        if (config('dynamicqr.ldap_enabled')) {
            return $this->attemptLdapAuthentication($normalizedUsername, $password, $remember);
        }

        if ($this->attemptLocalUser($normalizedUsername, $password, $remember)) {
            /** @var User|null $user */
            $user = Auth::user();

            return [true, $errorMessage, $user];
        }

        return [false, $errorMessage, null];
    }

    private function attemptLocalAccount(string $username, string $password, bool $remember): bool
    {
        if (! config('dynamicqr.local_account_enabled')) {
            return false;
        }

        return $this->attemptLocalUser($username, $password, $remember);
    }

    private function attemptLocalUser(string $username, string $password, bool $remember): bool
    {
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
     * @return array{0: bool, 1: string, 2: ?User}
     */
    private function attemptLdapAuthentication(string $username, string $password, bool $remember): array
    {
        $loginAttribute = (string) config('dynamicqr.ldap_login_attribute', 'samaccountname');

        try {
            if (! $this->ldapDirectoryAuthenticator->shouldUseDirectUserBind()) {
                $authenticated = Auth::attempt([
                    $loginAttribute => $username,
                    'password' => $password,
                ], $remember);

                if ($authenticated) {
                    /** @var User|null $user */
                    $user = Auth::user();

                    return [true, 'Kullanici adi veya sifre dogrulanamadi.', $user];
                }
            }

            $user = $this->ldapDirectoryAuthenticator->attempt($username, $password);

            if ($user) {
                Auth::login($user, $remember);

                return [true, 'Kullanici adi veya sifre dogrulanamadi.', $user];
            }

            return [
                false,
                $this->ldapDirectoryAuthenticator->lastErrorMessage() ?? 'Kullanici adi veya sifre dogrulanamadi.',
                null,
            ];
        } catch (BindException|ConnectionException $exception) {
            Log::warning('LDAP giris baglantisi basarisiz oldu.', [
                'username' => $username,
                'message' => $exception->getMessage(),
            ]);

            return [false, 'LDAP sunucusuna baglanilamadi. Sunucu ve bind ayarlarinizi kontrol edin.', null];
        } catch (Throwable $exception) {
            Log::error('LDAP giris akisi beklenmeyen bir hatayla karsilasti.', [
                'username' => $username,
                'message' => $exception->getMessage(),
            ]);

            return [false, 'LDAP girisi sirasinda beklenmeyen bir hata olustu.', null];
        }
    }
}
