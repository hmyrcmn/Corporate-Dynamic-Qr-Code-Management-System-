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
        $this->get('/')->assertOk()->assertSeeText('Kurumsal Dinamik QR Paneli');
        $this->get('/login')->assertOk()->assertSee('Güvenli personel girişi');
        $this->get('/admin/login')->assertNotFound();
    }

    public function test_guest_is_redirected_from_dashboard(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_seeded_local_account_can_log_in_from_login_form(): void
    {
        $this->seed();
        $token = 'test-token';

        $response = $this->withSession(['_token' => $token])->post('/login', [
            '_token' => $token,
            'username' => 'operator',
            'password' => 'ChangeMe123!',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();
        $this->assertSame('operator', auth()->user()?->username);
    }

    public function test_domain_qualified_username_can_log_in_to_seeded_local_account(): void
    {
        $this->seed();
        $token = 'test-token';

        $response = $this->withSession(['_token' => $token])->post('/login', [
            '_token' => $token,
            'username' => 'YEE\\operator',
            'password' => 'ChangeMe123!',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();
        $this->assertSame('operator', auth()->user()?->username);
    }

    public function test_seeded_local_account_is_department_manager(): void
    {
        $this->seed();

        $user = User::query()->where('username', 'operator')->firstOrFail();

        $this->assertSame(UserRole::DEPT_MANAGER->value, $user->role);
        $this->assertNotNull($user->department_id);
    }

    public function test_webmaster_can_log_in_without_department_assignment(): void
    {
        $token = 'test-token';

        User::create([
            'name' => 'Webmaster',
            'username' => 'webmaster',
            'email' => 'webmaster@yee.org.tr',
            'password' => 'Password123!',
            'department_id' => null,
            'role' => UserRole::DEPT_USER->value,
            'is_active' => true,
        ]);

        $response = $this->withSession(['_token' => $token])->post('/login', [
            '_token' => $token,
            'username' => 'webmaster@yee.org.tr',
            'password' => 'Password123!',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();
        $this->assertSame('webmaster@yee.org.tr', auth()->user()?->email);
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

    public function test_webmaster_sees_department_summary_cards_without_qr_titles_on_dashboard(): void
    {
        [$firstDepartment, $secondDepartment, $webmaster] = $this->createWebmasterScenario();

        QrCode::create([
            'department_id' => $firstDepartment->id,
            'created_by_id' => $webmaster->id,
            'title' => 'Kultur Kaydi',
            'destination_url' => 'https://www.yee.org.tr/kultur',
            'is_active' => true,
        ]);

        QrCode::create([
            'department_id' => $secondDepartment->id,
            'created_by_id' => $webmaster->id,
            'title' => 'Destek Kaydi',
            'destination_url' => 'https://www.yee.org.tr/destek',
            'is_active' => true,
        ]);

        $this->actingAs($webmaster)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Birim Merkezi')
            ->assertSee('Kultur')
            ->assertSee('Destek')
            ->assertSee('Toplam QR')
            ->assertSee('Tarama')
            ->assertSee('Aktif QR')
            ->assertSee('QR Kayıt')
            ->assertSee('Birim Sayfasını Aç')
            ->assertDontSee('Kultur Kaydi')
            ->assertDontSee('Destek Kaydi');
    }

    public function test_webmaster_can_open_selected_department_dashboard_and_only_see_that_departments_qr_codes(): void
    {
        [$firstDepartment, $secondDepartment, $webmaster] = $this->createWebmasterScenario();

        QrCode::create([
            'department_id' => $firstDepartment->id,
            'created_by_id' => $webmaster->id,
            'title' => 'Kultur Kaydi',
            'destination_url' => 'https://www.yee.org.tr/kultur',
            'is_active' => true,
        ]);

        QrCode::create([
            'department_id' => $secondDepartment->id,
            'created_by_id' => $webmaster->id,
            'title' => 'Destek Kaydi',
            'destination_url' => 'https://www.yee.org.tr/destek',
            'is_active' => true,
        ]);

        $this->actingAs($webmaster)
            ->get("/dashboard/departments/{$firstDepartment->id}")
            ->assertOk()
            ->assertSee('QR Yönetimi')
            ->assertSee('Kultur Kaydi')
            ->assertDontSee('Destek Kaydi');
    }

    public function test_authenticated_user_can_download_qr_as_svg(): void
    {
        $department = Department::create([
            'name' => 'Kultur ve Uluslararasi Programlar Birimi',
            'is_active' => true,
        ]);

        $user = User::create([
            'name' => 'Birim Kullanici',
            'username' => 'birim.kullanici',
            'email' => 'birim@example.test',
            'password' => 'Password123!',
            'department_id' => $department->id,
            'role' => UserRole::DEPT_USER->value,
            'is_active' => true,
        ]);

        $qrCode = QrCode::create([
            'department_id' => $department->id,
            'created_by_id' => $user->id,
            'title' => 'Uluslararasi kultur programi yonlendirme kaydi',
            'destination_url' => 'https://www.yee.org.tr/kultur',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)
            ->get("/download-qr/{$qrCode->short_id}?inline=1")
            ->assertOk()
            ->assertHeader('Content-Type', 'image/svg+xml')
            ->assertHeader('Content-Disposition', "inline; filename=\"qr-{$qrCode->short_id}.svg\"")
            ->assertSee('<svg', false)
            ->assertSee('Yunus Emre Enstitüsü Kurumsal QR', false)
            ->assertSee('BİRİM', false)
            ->assertDontSee('Dijital yönlendirme', false)
            ->assertDontSee('RESMİ ERİŞİM KARTI', false)
            ->assertDontSee('KURUMSAL QR', false)
            ->assertDontSee(strtoupper($qrCode->short_id), false)
            ->assertSee('<tspan x="380" y="754">Kultur ve Uluslararasi</tspan>', false)
            ->assertSee('<tspan x="380" y="779">Programlar Birimi</tspan>', false)
            ->assertSee('Uluslararasi kultur programi', false)
            ->assertSee('yonlendirme kaydi', false)
            ->assertSee('<tspan x="380" y="807">Uluslararasi kultur programi</tspan>', false)
            ->assertSee('data:image/png;base64,', false);

        $svg = $response->getContent();

        libxml_use_internal_errors(true);
        $parsed = simplexml_load_string($svg);
        $errors = libxml_get_errors();
        libxml_clear_errors();
        libxml_use_internal_errors(false);

        $this->assertNotFalse(
            $parsed,
            'Expected valid SVG XML, got: '.collect($errors)
                ->map(fn ($error) => trim($error->message))
                ->implode('; ')
        );
    }

    public function test_department_user_cannot_access_other_departments_qr_routes(): void
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

        $otherUser = User::create([
            'name' => 'Diger Kullanici',
            'username' => 'diger.kullanici',
            'email' => 'diger@example.test',
            'password' => 'Password123!',
            'department_id' => $otherDepartment->id,
            'role' => UserRole::DEPT_USER->value,
            'is_active' => true,
        ]);

        $foreignQrCode = QrCode::create([
            'department_id' => $otherDepartment->id,
            'created_by_id' => $otherUser->id,
            'title' => 'Destek Kaydi',
            'destination_url' => 'https://www.yee.org.tr/destek',
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->get("/dashboard/departments/{$otherDepartment->id}")
            ->assertNotFound();

        $this->actingAs($user)
            ->get("/dashboard/edit/{$foreignQrCode->short_id}")
            ->assertNotFound();

        $this->actingAs($user)
            ->get("/dashboard/delete/{$foreignQrCode->short_id}")
            ->assertNotFound();

        $this->actingAs($user)
            ->get("/download-qr/{$foreignQrCode->short_id}")
            ->assertNotFound();
    }

    public function test_webmaster_can_manage_qr_codes_after_selecting_department(): void
    {
        [$firstDepartment, $secondDepartment, $webmaster] = $this->createWebmasterScenario();

        $departmentUser = User::create([
            'name' => 'Diger Kullanici',
            'username' => 'diger.kullanici',
            'email' => 'diger@example.test',
            'password' => 'Password123!',
            'department_id' => $firstDepartment->id,
            'role' => UserRole::DEPT_USER->value,
            'is_active' => true,
        ]);

        $foreignQrCode = QrCode::create([
            'department_id' => $firstDepartment->id,
            'created_by_id' => $departmentUser->id,
            'title' => 'Kultur Kaydi',
            'destination_url' => 'https://www.yee.org.tr/kultur',
            'is_active' => true,
        ]);

        $this->actingAs($webmaster)
            ->post("/dashboard/departments/{$secondDepartment->id}/create", [
                'title' => 'Destek Icin Yeni Kayit',
                'destination_url' => 'https://www.yee.org.tr/destek/yeni',
                'is_active' => 1,
            ])
            ->assertRedirect("/dashboard/departments/{$secondDepartment->id}");

        $this->assertDatabaseHas('qr_codes', [
            'department_id' => $secondDepartment->id,
            'title' => 'Destek Icin Yeni Kayit',
        ]);

        $this->actingAs($webmaster)
            ->put("/dashboard/departments/{$firstDepartment->id}/edit/{$foreignQrCode->short_id}", [
                'title' => 'Guncellenen Kayit',
                'destination_url' => 'https://www.yee.org.tr/kultur/guncel',
                'is_active' => 1,
            ])
            ->assertRedirect("/dashboard/departments/{$firstDepartment->id}");

        $this->assertDatabaseHas('qr_codes', [
            'id' => $foreignQrCode->id,
            'department_id' => $firstDepartment->id,
            'title' => 'Guncellenen Kayit',
        ]);
    }

    public function test_webmaster_cannot_use_generic_qr_routes_without_selecting_department(): void
    {
        [$firstDepartment, $secondDepartment, $webmaster] = $this->createWebmasterScenario();

        $qrCode = QrCode::create([
            'department_id' => $firstDepartment->id,
            'created_by_id' => $webmaster->id,
            'title' => 'Kultur Kaydi',
            'destination_url' => 'https://www.yee.org.tr/kultur',
            'is_active' => true,
        ]);

        $this->actingAs($webmaster)
            ->get('/dashboard/create')
            ->assertRedirect('/dashboard');

        $this->actingAs($webmaster)
            ->get("/dashboard/edit/{$qrCode->short_id}")
            ->assertRedirect('/dashboard');

        $this->actingAs($webmaster)
            ->get("/download-qr/{$qrCode->short_id}")
            ->assertRedirect('/dashboard');
    }

    public function test_admin_routes_are_not_available(): void
    {
        $this->seed();

        $user = User::query()->where('username', 'operator')->firstOrFail();

        $this->actingAs($user)
            ->get('/admin')
            ->assertNotFound();

        $this->actingAs($user)
            ->get('/admin/login')
            ->assertNotFound();
    }

    /**
     * @return array{Department, Department, User}
     */
    private function createWebmasterScenario(): array
    {
        $firstDepartment = Department::create([
            'name' => 'Kultur',
            'is_active' => true,
        ]);

        $secondDepartment = Department::create([
            'name' => 'Destek',
            'is_active' => true,
        ]);

        $webmaster = User::create([
            'name' => 'Webmaster',
            'username' => 'webmaster',
            'email' => 'webmaster@yee.org.tr',
            'password' => 'Password123!',
            'department_id' => null,
            'role' => UserRole::DEPT_USER->value,
            'is_active' => true,
        ]);

        return [$firstDepartment, $secondDepartment, $webmaster];
    }
}
