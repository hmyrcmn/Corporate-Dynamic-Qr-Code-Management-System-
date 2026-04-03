<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Ldap\User as LdapUser;
use App\Models\Department;
use App\Models\User;
use App\Support\LdapUsername;
use Illuminate\Support\Str;
use LdapRecord\Auth\BindException;
use LdapRecord\Connection;
use LdapRecord\ConnectionException;
use LdapRecord\Container;
use Throwable;

class LdapDirectoryAuthenticator
{
    protected ?string $lastErrorMessage = null;

    public function attempt(string $inputUsername, string $password): ?User
    {
        $this->lastErrorMessage = null;

        $normalizedUsername = LdapUsername::normalize($inputUsername);

        if ($normalizedUsername === '' || trim($password) === '') {
            return null;
        }

        $directoryUser = $this->authenticateAndFetchProfile($inputUsername, $normalizedUsername, $password);

        if (! $directoryUser) {
            return null;
        }

        return $this->synchronizeLocalUser($directoryUser);
    }

    public function lastErrorMessage(): ?string
    {
        return $this->lastErrorMessage;
    }

    public function shouldUseDirectUserBind(): bool
    {
        return config('dynamicqr.ldap_force_user_bind') || ! $this->hasServiceCredentials();
    }

    protected function hasServiceCredentials(): bool
    {
        $connectionName = (string) config('ldap.default', 'default');
        $connection = (array) config("ldap.connections.{$connectionName}", []);

        return filled($connection['username'] ?? null) && filled($connection['password'] ?? null);
    }

    protected function authenticateAndFetchProfile(string $inputUsername, string $normalizedUsername, string $password): ?array
    {
        $connectionName = (string) config('ldap.default', 'default');
        $baseDn = (string) config("ldap.connections.{$connectionName}.base_dn", '');

        if ($baseDn === '') {
            $this->lastErrorMessage = 'LDAP base DN tanimli degil.';

            return null;
        }

        try {
            return Container::getConnection($connectionName)->isolate(
                function (Connection $connection) use ($inputUsername, $normalizedUsername, $password, $baseDn): ?array {
                    foreach ($this->bindCandidates($inputUsername, $normalizedUsername) as $bindUsername) {
                        try {
                            $connection->connect($bindUsername, $password);
                        } catch (BindException) {
                            continue;
                        } catch (ConnectionException $exception) {
                            $this->lastErrorMessage = 'LDAP sunucusuna baglanilamadi: '.$exception->getMessage();

                            return null;
                        }

                        $profile = $this->findDirectoryUser($connection, $baseDn, $inputUsername, $normalizedUsername);

                        if ($profile) {
                            return $profile;
                        }
                    }

                    return null;
                }
            );
        } catch (Throwable $exception) {
            $this->lastErrorMessage = 'LDAP baglantisi sirasinda beklenmeyen bir hata olustu: '.$exception->getMessage();

            return null;
        }
    }

    protected function findDirectoryUser(Connection $connection, string $baseDn, string $inputUsername, string $normalizedUsername): ?array
    {
        foreach ($this->searchCandidates($inputUsername, $normalizedUsername) as [$attribute, $value]) {
            /** @var LdapUser|null $ldapUser */
            $ldapUser = $connection->query()
                ->setBaseDn($baseDn)
                ->model(new LdapUser())
                ->whereEquals($attribute, $value)
                ->first();

            if ($ldapUser) {
                return [
                    'guid' => $ldapUser->getConvertedGuid(),
                    'username' => LdapUsername::normalize((string) ($ldapUser->getFirstAttribute(config('dynamicqr.ldap_login_attribute')) ?? $normalizedUsername)),
                    'name' => trim((string) ($ldapUser->getFirstAttribute(config('dynamicqr.ldap_display_attribute')) ?? $ldapUser->getFirstAttribute('cn') ?? $normalizedUsername)),
                    'email' => trim((string) ($ldapUser->getFirstAttribute(config('dynamicqr.ldap_email_attribute')) ?? '')),
                    'department' => trim((string) ($ldapUser->getFirstAttribute(config('dynamicqr.ldap_department_attribute')) ?? '')),
                    'is_active' => method_exists($ldapUser, 'isDisabled') ? ! $ldapUser->isDisabled() : true,
                    'domain' => $this->resolveDomainFqdn(),
                ];
            }
        }

        $this->lastErrorMessage = 'LDAP kullanicisi bulundu ancak profil bilgileri okunamadi.';

        return null;
    }

    protected function synchronizeLocalUser(array $directoryUser): User
    {
        $department = null;

        if (($directoryUser['department'] ?? '') !== '') {
            $department = Department::firstOrCreate(
                ['name' => $directoryUser['department']],
                ['is_active' => true],
            );
        }

        $user = User::query()
            ->where(function ($query) use ($directoryUser): void {
                if (filled($directoryUser['guid'] ?? null)) {
                    $query->orWhere('guid', $directoryUser['guid']);
                }

                $query->orWhere('username', $directoryUser['username']);

                if (filled($directoryUser['email'] ?? null)) {
                    $query->orWhere('email', $directoryUser['email']);
                }
            })
            ->first() ?? new User();

        $user->forceFill([
            'guid' => $directoryUser['guid'] ?: $user->guid,
            'domain' => $directoryUser['domain'] ?: $user->domain,
            'username' => $directoryUser['username'],
            'name' => $directoryUser['name'] !== '' ? $directoryUser['name'] : $user->name,
            'email' => $directoryUser['email'] !== '' ? $directoryUser['email'] : $user->email,
            'department_id' => $department?->id,
            'role' => UserRole::DEPT_USER->value,
            'is_active' => (bool) ($directoryUser['is_active'] ?? true),
        ])->saveQuietly();

        return $user->fresh() ?? $user;
    }

    protected function bindCandidates(string $inputUsername, string $normalizedUsername): array
    {
        $candidates = [trim($inputUsername), $normalizedUsername];

        if ($domainFqdn = $this->resolveDomainFqdn()) {
            $candidates[] = $normalizedUsername.'@'.$domainFqdn;
        }

        if ($netbiosDomain = $this->resolveNetbiosDomain()) {
            $candidates[] = $netbiosDomain.'\\'.$normalizedUsername;
        }

        return array_values(array_unique(array_filter($candidates)));
    }

    protected function searchCandidates(string $inputUsername, string $normalizedUsername): array
    {
        $loginAttribute = (string) config('dynamicqr.ldap_login_attribute', 'samaccountname');
        $emailAttribute = (string) config('dynamicqr.ldap_email_attribute', 'mail');

        $candidates = [
            [$loginAttribute, $normalizedUsername],
        ];

        $trimmedInput = trim($inputUsername);

        if (str_contains($trimmedInput, '@')) {
            $candidates[] = [$emailAttribute, mb_strtolower($trimmedInput)];
        }

        return array_values(array_unique($candidates, SORT_REGULAR));
    }

    protected function resolveDomainFqdn(): string
    {
        $configured = trim((string) config('dynamicqr.ldap_domain'));

        if ($configured !== '') {
            return mb_strtolower($configured);
        }

        preg_match_all('/DC=([^,]+)/i', (string) config('ldap.connections.'.config('ldap.default', 'default').'.base_dn'), $matches);

        return isset($matches[1]) && count($matches[1]) > 0
            ? mb_strtolower(implode('.', $matches[1]))
            : '';
    }

    protected function resolveNetbiosDomain(): string
    {
        $configured = trim((string) config('dynamicqr.ldap_netbios_domain'));

        if ($configured !== '') {
            return strtoupper($configured);
        }

        $domainFqdn = $this->resolveDomainFqdn();

        if ($domainFqdn === '') {
            return '';
        }

        return strtoupper((string) Str::before($domainFqdn, '.'));
    }
}
