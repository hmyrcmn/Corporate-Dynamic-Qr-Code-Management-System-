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
        if (! config('dynamicqr.local_super_admin_enabled')) {
            return;
        }

        $localUsername = LdapUsername::normalize((string) config('dynamicqr.local_super_admin_username'));
        $ldapSuperAdminUsername = LdapUsername::normalize((string) config('dynamicqr.super_admin_username'));
        $seedAsSuperAdmin = $ldapSuperAdminUsername === '' || strcasecmp($ldapSuperAdminUsername, $localUsername) === 0;

        $department = Department::firstOrCreate(
            ['name' => 'Bilgi Teknolojileri'],
            ['is_active' => true],
        );

        User::updateOrCreate(
            ['username' => $localUsername],
            [
                'name' => $seedAsSuperAdmin ? 'Yerel Super Admin' : 'Yerel Acil Durum Hesabi',
                'email' => 'admin@dynamicqr.local',
                'password' => config('dynamicqr.local_super_admin_password'),
                'department_id' => $department->id,
                'role' => $seedAsSuperAdmin
                    ? UserRole::SUPER_ADMIN->value
                    : UserRole::DEPT_MANAGER->value,
                'is_active' => true,
            ],
        );
    }
}
