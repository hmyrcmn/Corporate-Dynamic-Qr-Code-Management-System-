<?php

namespace App\Providers;

use App\Enums\UserRole;
use App\Models\Department;
use App\Models\User;
use App\Providers\Filament\AdminPanelProvider;
use App\Support\LdapUsername;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use LdapRecord\Laravel\Events\Import\Imported;
use LdapRecord\Laravel\Events\Import\Synchronized;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if ($this->shouldRegisterAdminPanel()) {
            $this->app->register(AdminPanelProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $syncUser = function ($event): void {
            /** @var User $user */
            $user = $event->eloquent;
            $ldapUser = $event->object;

            $loginAttribute = (string) config('dynamicqr.ldap_login_attribute', 'samaccountname');
            $displayAttribute = (string) config('dynamicqr.ldap_display_attribute', 'displayname');
            $emailAttribute = (string) config('dynamicqr.ldap_email_attribute', 'mail');
            $departmentAttribute = (string) config('dynamicqr.ldap_department_attribute', 'department');

            $departmentName = trim((string) ($ldapUser->getFirstAttribute($departmentAttribute) ?? ''));
            $username = LdapUsername::normalize((string) ($ldapUser->getFirstAttribute($loginAttribute) ?? $user->username));
            $displayName = trim((string) ($ldapUser->getFirstAttribute($displayAttribute) ?? $ldapUser->getFirstAttribute('cn') ?? $user->name));
            $email = trim((string) ($ldapUser->getFirstAttribute($emailAttribute) ?? $user->email));
            $superAdminUsername = LdapUsername::normalize((string) config('dynamicqr.super_admin_username'));
            $isActive = method_exists($ldapUser, 'isDisabled') ? ! $ldapUser->isDisabled() : true;

            $attributes = [
                'username' => $username !== '' ? $username : $user->username,
                'name' => $displayName !== '' ? $displayName : $user->name,
                'email' => $email !== '' ? $email : $user->email,
                'is_active' => $isActive,
                'department_id' => null,
                'role' => ($superAdminUsername !== '' && strcasecmp($username, $superAdminUsername) === 0)
                    ? UserRole::SUPER_ADMIN->value
                    : UserRole::DEPT_USER->value,
            ];

            if ($departmentName !== '') {
                $department = Department::firstOrCreate(
                    ['name' => $departmentName],
                    ['is_active' => true],
                );

                $attributes['department_id'] = $department->id;
            }

            $user->forceFill($attributes)->saveQuietly();
        };

        Event::listen(Imported::class, $syncUser);
        Event::listen(Synchronized::class, $syncUser);
    }

    private function shouldRegisterAdminPanel(): bool
    {
        if ($this->app->runningInConsole()) {
            return true;
        }

        if (! $this->app->bound('request')) {
            return false;
        }

        return $this->app['request']->is('admin') || $this->app['request']->is('admin/*');
    }
}
