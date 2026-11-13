<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AdminAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_add_another_admin_without_touching_the_database(): void
    {
        Notification::fake();
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->post(route('admin.admins.store'), [
            'name' => 'New Admin',
            'email' => 'New.Admin@Example.test',
        ])->assertRedirect();

        $new = User::where('email', 'new.admin@example.test')->firstOrFail();
        $this->assertSame('admin', $new->role);
        $this->assertTrue($new->is_admin);
        $this->assertNull($new->email_verified_at);
        Notification::assertSentTo($new, ResetPassword::class);
    }

    public function test_clients_and_attorneys_cannot_add_admins(): void
    {
        $client = User::factory()->create(['is_admin' => false]);

        $this->actingAs($client)->post(route('admin.admins.store'), [
            'name' => 'Sneaky',
            'email' => 'sneaky@example.test',
        ])->assertForbidden();

        $this->assertDatabaseMissing('users', ['email' => 'sneaky@example.test']);
    }

    public function test_duplicate_emails_are_rejected(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'email' => 'taken@example.test']);

        $this->actingAs($admin)->post(route('admin.admins.store'), [
            'name' => 'Duplicate',
            'email' => 'TAKEN@example.test',
        ])->assertSessionHasErrors('email');
    }

    public function test_admin_cannot_set_role_or_flags_through_the_form(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->post(route('admin.admins.store'), [
            'name' => 'Extra',
            'email' => 'extra@example.test',
            'role' => 'admin',
        ])->assertStatus(422);
    }

    public function test_deactivated_admin_loses_access_and_cannot_sign_in(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $colleague = User::factory()->create(['is_admin' => true, 'email' => 'colleague@example.test']);

        $this->actingAs($admin)->post(route('admin.admins.deactivate', $colleague))->assertRedirect();
        $colleague->refresh();
        $this->assertTrue($colleague->isDeactivated());
        $this->assertFalse($colleague->isAdministrator());
        $this->assertFalse($colleague->isClient());

        $this->actingAs($colleague)->get(route('admin.dashboard'))->assertForbidden();

        auth()->logout();
        $this->post(route('admin.login.store'), ['email' => 'colleague@example.test', 'password' => 'password'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_admin_cannot_deactivate_themselves(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->post(route('admin.admins.deactivate', $admin))
            ->assertSessionHasErrors('admin_account');

        $this->assertFalse($admin->refresh()->isDeactivated());
    }

    public function test_deactivated_admin_can_be_reactivated(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $colleague = User::factory()->create(['is_admin' => true, 'deactivated_at' => now()]);

        $this->actingAs($admin)->post(route('admin.admins.reactivate', $colleague))->assertRedirect();

        $this->assertTrue($colleague->refresh()->isAdministrator());
    }

    public function test_non_admin_accounts_cannot_be_targeted_by_admin_actions(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $client = User::factory()->create(['is_admin' => false]);

        $this->actingAs($admin)->post(route('admin.admins.deactivate', $client))->assertNotFound();
        $this->assertFalse($client->refresh()->isDeactivated());
    }

    public function test_setup_link_can_be_resent_only_while_setup_is_pending(): void
    {
        Notification::fake();
        $admin = User::factory()->create(['is_admin' => true]);
        $pending = User::factory()->unverified()->create(['is_admin' => true]);
        $done = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->post(route('admin.admins.invite.resend', $pending))->assertRedirect();
        Notification::assertSentTo($pending, ResetPassword::class);

        $this->actingAs($admin)->post(route('admin.admins.invite.resend', $done))
            ->assertSessionHasErrors('admin_account');
        Notification::assertNotSentTo($done, ResetPassword::class);
    }

    public function test_dashboard_lists_administrator_accounts(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'name' => 'Lead Admin']);

        $this->actingAs($admin)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Administrator accounts')
            ->assertSee('Lead Admin');
    }

    public function test_artisan_command_creates_the_first_admin(): void
    {
        $this->artisan('admin:create', ['--name' => 'First Admin', '--email' => 'first@example.test'])
            ->expectsQuestion('Password (min 12 chars, mixed case, a number and a symbol)', 'StrongPassw0rd!')
            ->expectsQuestion('Confirm password', 'StrongPassw0rd!')
            ->assertSuccessful();

        $user = User::where('email', 'first@example.test')->firstOrFail();
        $this->assertSame('admin', $user->role);
        $this->assertTrue($user->isAdministrator());
        $this->assertTrue(Hash::check('StrongPassw0rd!', $user->password));
        $this->assertNotNull($user->email_verified_at);
    }

    public function test_artisan_command_rejects_weak_passwords_and_existing_emails(): void
    {
        $this->artisan('admin:create', ['--name' => 'Weak', '--email' => 'weak@example.test'])
            ->expectsQuestion('Password (min 12 chars, mixed case, a number and a symbol)', 'short')
            ->expectsQuestion('Confirm password', 'short')
            ->assertFailed();
        $this->assertDatabaseMissing('users', ['email' => 'weak@example.test']);

        User::factory()->create(['email' => 'exists@example.test']);
        $this->artisan('admin:create', ['--name' => 'Dup', '--email' => 'exists@example.test'])->assertFailed();
    }

    public function test_artisan_invite_option_emails_a_setup_link(): void
    {
        Notification::fake();

        $this->artisan('admin:create', ['--name' => 'Invited', '--email' => 'invited@example.test', '--invite' => true])
            ->assertSuccessful();

        $user = User::where('email', 'invited@example.test')->firstOrFail();
        $this->assertNull($user->email_verified_at);
        Notification::assertSentTo($user, ResetPassword::class);
    }
}
