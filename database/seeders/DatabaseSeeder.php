<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Department;
use App\Models\User;
use App\Support\LdapUsername;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::query()
            ->whereNotIn('role', [
                UserRole::DEPT_MANAGER->value,
                UserRole::DEPT_USER->value,
            ])
            ->update(['role' => UserRole::DEPT_MANAGER->value]);

        if (! config('dynamicqr.local_account_enabled')) {
            return;
        }

        $localUsername = LdapUsername::normalize((string) config('dynamicqr.local_account_username'));

        $department = Department::firstOrCreate(
            ['name' => 'Bilgi Teknolojileri'],
            ['is_active' => true],
        );

        $user = User::query()
            ->where('username', $localUsername)
            ->orWhere('email', 'operator@dynamicqr.local')
            ->orWhere(function ($query): void {
                $query
                    ->whereNull('guid')
                    ->whereNull('domain')
                    ->where('email', 'like', '%@dynamicqr.local');
            })
            ->first() ?? new User();

        $user->forceFill([
            'name' => 'Yerel Kullanici',
            'username' => $localUsername,
            'email' => 'operator@dynamicqr.local',
            'password' => config('dynamicqr.local_account_password'),
            'department_id' => $department->id,
            'role' => UserRole::DEPT_MANAGER->value,
            'is_active' => true,
        ])->saveQuietly();
    }
}
