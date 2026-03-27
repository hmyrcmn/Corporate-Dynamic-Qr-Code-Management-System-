<?php

use App\Ldap\User as LdapUser;
use App\Support\LdapUsername;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Console\Command\Command;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('dynamicqr:ldap:lookup {username : LDAP kullanici adi}', function (string $username): int {
    if (! config('dynamicqr.ldap_enabled')) {
        $this->error('LDAP devre disi. Once .env icinde LDAP_ENABLED=true yapin.');

        return Command::FAILURE;
    }

    $normalizedUsername = LdapUsername::normalize($username);
    $loginAttribute = (string) config('dynamicqr.ldap_login_attribute', 'samaccountname');
    $displayAttribute = (string) config('dynamicqr.ldap_display_attribute', 'displayname');
    $emailAttribute = (string) config('dynamicqr.ldap_email_attribute', 'mail');
    $departmentAttribute = (string) config('dynamicqr.ldap_department_attribute', 'department');

    try {
        $ldapUser = LdapUser::query()
            ->whereEquals($loginAttribute, $normalizedUsername)
            ->first();
    } catch (\Throwable $exception) {
        $this->error('LDAP sorgusu basarisiz: '.$exception->getMessage());

        return Command::FAILURE;
    }

    if (! $ldapUser) {
        $this->warn("Kullanici bulunamadi: {$normalizedUsername}");

        return Command::INVALID;
    }

    $this->table(['Alan', 'Deger'], [
        ['Kullanici adi', (string) ($ldapUser->getFirstAttribute($loginAttribute) ?? $normalizedUsername)],
        ['Ad soyad', (string) ($ldapUser->getFirstAttribute($displayAttribute) ?? $ldapUser->getFirstAttribute('cn') ?? '-')],
        ['E-posta', (string) ($ldapUser->getFirstAttribute($emailAttribute) ?? '-')],
        ['Birim', (string) ($ldapUser->getFirstAttribute($departmentAttribute) ?? '-')],
        ['Durum', method_exists($ldapUser, 'isEnabled') ? ($ldapUser->isEnabled() ? 'Aktif' : 'Pasif') : 'Bilinmiyor'],
        ['DN', (string) $ldapUser->getDn()],
    ]);

    return Command::SUCCESS;
})->purpose('Tek bir LDAP kullanicisini baglanti, durum ve birim bilgisiyle dogrula');
