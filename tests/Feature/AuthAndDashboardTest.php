<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Department;
use App\Models\QrCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthAndDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_pages_are_reachable(): void
    {
        $this->get('/')->assertOk()->assertSee('QR surecini birkac adimda baslatin.');
        $this->get('/login')->assertOk()->assertSee('Guvenli personel girisi');
        $this->get('/admin/login')->assertOk();
    }

    public function test_guest_is_redirected_from_dashboard(): void
    {
        $this->get('/dashboard')
            ->assertRedirect('/login');
    }

    public function test_seeded_local_super_admin_can_log_in_from_login_form(): void
    {
        $this->seed();

        $response = $this->post('/login', [
            'username' => 'admin',
            'password' => 'ChangeMe123!',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();
        $this->assertSame('admin', auth()->user()?->username);
    }

    public function test_domain_qualified_username_can_log_in_to_seeded_local_account(): void
    {
        $this->seed();

        $response = $this->post('/login', [
            'username' => 'YEE\\admin',
            'password' => 'ChangeMe123!',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();
        $this->assertSame('admin', auth()->user()?->username);
    }

    public function test_seeded_local_account_is_demoted_when_ldap_super_admin_is_configured(): void
    {
        config(['dynamicqr.super_admin_username' => 'ldap.admin']);

        $this->seed();

        $user = User::query()->where('username', 'admin')->firstOrFail();

        $this->assertSame(UserRole::DEPT_MANAGER->value, $user->role);
    }

    public function test_department_user_only_sees_own_department_qr_codes(): void
    {
        $ownDepartment = Department::create([
            'name' => 'Kultur',
            'is_active' => true,
        ]);

        $otherDepartment = Department::create([
            'name' => 'Destek',
            'is_active' => true,
        ]);

        $user = User::create([
            'name' => 'Birim Kullanici',
            'username' => 'birim.kullanici',
            'email' => 'birim@example.test',
            'password' => 'Password123!',
            'department_id' => $ownDepartment->id,
            'role' => UserRole::DEPT_USER->value,
            'is_active' => true,
        ]);

        QrCode::create([
            'department_id' => $ownDepartment->id,
            'created_by_id' => $user->id,
            'title' => 'Kultur Kaydi',
            'destination_url' => 'https://www.yee.org.tr/kultur',
            'is_active' => true,
        ]);

        QrCode::create([
            'department_id' => $otherDepartment->id,
            'created_by_id' => $user->id,
            'title' => 'Destek Kaydi',
            'destination_url' => 'https://www.yee.org.tr/destek',
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Kultur Kaydi')
            ->assertDontSee('Destek Kaydi');
    }
}
