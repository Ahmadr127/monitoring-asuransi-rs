<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_is_accessible()
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Login');
    }

    public function test_admin_can_login_and_redirect_to_dashboard()
    {
        $user = User::factory()->admin()->create([
            'username' => 'admin',
            'password' => bcrypt('password'),
        ]);

        $response = $this->post('/login', [
            'username' => 'admin',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticated();
    }

    public function test_legal_can_login_and_redirect_to_dashboard()
    {
        $user = User::factory()->nonAdmin()->create([
            'username' => 'staff',
            'password' => bcrypt('password'),
        ]);

        $response = $this->post('/login', [
            'username' => 'staff',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('user.dashboard'));
        $this->assertAuthenticated();
    }

    public function test_login_fails_with_wrong_credentials()
    {
        User::factory()->create([
            'username' => 'admin',
            'password' => bcrypt('password'),
        ]);

        $response = $this->post('/login', [
            'username' => 'admin',
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHas('error');
        $this->assertGuest();
    }

    public function test_login_validates_required_fields()
    {
        $response = $this->post('/login', []);

        $response->assertSessionHasErrors(['username', 'password']);
    }

    public function test_logout()
    {
        $user = User::factory()->admin()->create();
        $this->actingAs($user);

        $response = $this->post('/logout');

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_admin_cannot_access_user_routes()
    {
        $this->loginAsAdmin();

        $response = $this->get('/user/kerjasama');

        $response->assertStatus(403);
    }

    public function test_non_admin_cannot_access_admin_routes()
    {
        $this->loginAsNonAdmin();

        $response = $this->get('/admin/dashboard');

        $response->assertStatus(403);
    }

    public function test_guest_redirected_to_login()
    {
        $response = $this->get('/admin/dashboard');

        $response->assertRedirect(route('login'));
    }

    public function test_root_redirects_based_on_role()
    {
        $this->loginAsAdmin();
        $response = $this->get('/');
        $response->assertRedirect(route('admin.dashboard'));

        $this->post('/logout');

        $this->loginAsNonAdmin();
        $response = $this->get('/');
        $response->assertRedirect(route('user.dashboard'));
    }

    public function test_guest_root_redirects_to_login()
    {
        $response = $this->get('/');

        $response->assertRedirect(route('login'));
    }
}
