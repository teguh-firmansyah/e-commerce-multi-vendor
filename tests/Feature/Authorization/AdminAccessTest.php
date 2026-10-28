<?php

namespace Tests\Feature\Authorization;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login_from_admin_area(): void
    {
        $this->get('/admin/dashboard')->assertRedirect(route('login'));
    }

    public function test_customers_cannot_access_admin_area(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/admin/dashboard')
            ->assertForbidden();
    }

    public function test_restricted_admins_cannot_access_admin_area(): void
    {
        $this->actingAs(User::factory()->admin()->restricted()->create())
            ->get('/admin/dashboard')
            ->assertForbidden();
    }

    public function test_active_admins_can_access_admin_area(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get('/admin/dashboard')
            ->assertOk();
    }

    public function test_disabled_users_are_logged_out_on_next_request(): void
    {
        $this->actingAs(User::factory()->disabled()->create())
            ->get('/dashboard')
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }
}
