<?php

namespace Tests\Feature;

use App\Models\Office;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class HadirkuTest extends TestCase
{
    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_login_page_loads_successfully(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('HAdirKu');
        $response->assertDontSee('VERSI PEGAWAI');
    }

    public function test_user_can_login_with_email(): void
    {
        $user = User::where('role', 'pegawai')->first();

        $response = $this->post('/login', [
            'login' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_authenticated_user_can_view_dashboard(): void
    {
        $user = User::where('role', 'pegawai')->first();
        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee("Today's Status", false);
        $response->assertSee('Attendance Overview');
        $response->assertSee('This Month Summary');
        $response->assertSee('Recent Activities');
        $response->assertSee('Punch');
    }

    public function test_authenticated_user_can_view_history(): void
    {
        $user = User::where('role', 'pegawai')->first();
        $response = $this->actingAs($user)->get('/history');

        $response->assertStatus(200);
        $response->assertSee('Attendance History');
        $response->assertSee('Total Hours');
    }

    public function test_user_punch_outside_radius_is_rejected(): void
    {
        $user = User::where('role', 'pegawai')->first();
        $office = $user->office ?: Office::first();

        // Far coordinates (e.g. 50km away)
        $response = $this->actingAs($user)->postJson('/punch', [
            'type' => 'check_in',
            'latitude' => $office->latitude + 0.5,
            'longitude' => $office->longitude + 0.5,
            'photo' => 'data:image/jpeg;base64,' . base64_encode('fake_image_bytes'),
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
        ]);
        $response->assertSee('radius kantor');
    }

    public function test_user_punch_within_radius_succeeds(): void
    {
        $user = User::where('email', 'budi@hadirku.com')->first();
        $office = $user->office ?: Office::first();

        // Clear today's attendance for clean test run
        \App\Models\Attendance::where('user_id', $user->id)->whereDate('date', now()->toDateString())->delete();

        // Exact office coordinates
        $response = $this->actingAs($user)->postJson('/punch', [
            'type' => 'check_in',
            'latitude' => $office->latitude,
            'longitude' => $office->longitude,
            'photo' => 'data:image/jpeg;base64,' . base64_encode('fake_image_bytes'),
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
    }

    public function test_admin_can_access_filament_panel(): void
    {
        $admin = User::where('role', 'admin')->first();
        $response = $this->actingAs($admin)->get('/admin');

        $response->assertStatus(200);
    }
}
