<?php

namespace Tests\Feature\Authorization;

use App\Enums\AdminPermission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class AdminPermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_without_permission_is_denied(): void
    {
        $admin = User::factory()->admin()->create();

        $this->assertTrue(Gate::forUser($admin)->denies('payout.approve'));
        $this->assertTrue(Gate::forUser($admin)->denies('refund.approve'));
    }

    public function test_admin_is_allowed_only_for_granted_permission(): void
    {
        $admin = User::factory()->admin()->withPermissions(AdminPermission::ApprovePayout)->create();

        $this->assertTrue(Gate::forUser($admin)->allows('payout.approve'));
        $this->assertTrue(Gate::forUser($admin)->denies('refund.approve'));
    }

    public function test_customer_with_permissions_is_denied(): void
    {
        $user = User::factory()->withPermissions(AdminPermission::ApprovePayout)->create();

        $this->assertTrue(Gate::forUser($user)->denies('payout.approve'));
    }

    public function test_restricted_admin_with_permission_is_denied(): void
    {
        $admin = User::factory()
            ->admin()
            ->restricted()
            ->withPermissions(AdminPermission::ApproveRefund)
            ->create();

        $this->assertTrue(Gate::forUser($admin)->denies('refund.approve'));
    }
}
