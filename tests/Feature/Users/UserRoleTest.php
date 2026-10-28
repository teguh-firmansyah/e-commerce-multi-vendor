<?php

namespace Tests\Feature\Users;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_are_active_customers_by_default(): void
    {
        $user = User::factory()->create()->refresh();

        $this->assertSame(UserRole::Customer, $user->role);
        $this->assertSame(UserStatus::Active, $user->status);
        $this->assertFalse($user->isAdmin());
        $this->assertTrue($user->isActive());
    }

    public function test_factory_provides_admin_and_status_states(): void
    {
        $this->assertTrue(User::factory()->admin()->create()->isAdmin());
        $this->assertSame(UserStatus::Restricted, User::factory()->restricted()->create()->status);
        $this->assertFalse(User::factory()->disabled()->create()->isActive());
    }

    public function test_role_and_status_input_is_ignored_during_registration(): void
    {
        $this->post('/register', [
            'name' => 'Pengguna Baru',
            'email' => 'baru@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'admin',
            'status' => 'disabled',
        ]);

        $user = User::where('email', 'baru@example.com')->firstOrFail();

        $this->assertSame(UserRole::Customer, $user->role);
        $this->assertSame(UserStatus::Active, $user->status);
    }
}
