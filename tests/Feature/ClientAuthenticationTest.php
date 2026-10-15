<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class ClientAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_gateway_separates_admin_client_and_demo_access(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee(route('admin.login'))
            ->assertSee(route('client.login'))
            ->assertSee(route('client.register'))
            ->assertSee(route('consultation'));

        $this->get(route('client.login'))->assertOk()->assertSee('Existing client login');
        $this->get(route('client.register'))->assertOk()->assertSee('Register as a new user');
        $this->get(route('admin.login'))->assertOk()->assertSee('Administrative sign in');
        $this->get(route('consultation'))->assertOk();
        $this->get(route('client-portal'))->assertRedirect(route('login'));
    }

    public function test_registration_hashes_password_and_never_accepts_admin_role(): void
    {
        Notification::fake();
        $form = [
            'name' => 'Client Example',
            'email' => 'Client.Example@example.test',
            'password' => 'StrongPassword123!',
            'password_confirmation' => 'StrongPassword123!',
        ];

        $this->post(route('client.register.store'), $form + ['is_admin' => true])
            ->assertUnprocessable();
        $this->assertDatabaseMissing('users', ['email' => 'client.example@example.test']);

        $this->post(route('client.register.store'), $form)
            ->assertRedirect(route('verification.notice'));

        $user = User::where('email', 'client.example@example.test')->firstOrFail();
        $this->assertFalse($user->is_admin);
        $this->assertNotSame('StrongPassword123!', $user->password);
        $this->assertAuthenticatedAs($user);
        Notification::assertSentTo($user, VerifyEmail::class);
        $this->get(route('verification.notice'))->assertOk()->assertSee($user->email);
        $this->get(route('client-portal'))->assertRedirect(route('verification.notice'));
    }

    public function test_signed_email_verification_enables_client_portal(): void
    {
        $client = User::factory()->unverified()->create(['is_admin' => false]);
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(30),
            ['id' => $client->id, 'hash' => sha1($client->getEmailForVerification())]
        );

        $this->actingAs($client)->get($verificationUrl)->assertRedirect(route('client-portal'));
        $this->assertNotNull($client->fresh()->email_verified_at);
    }

    public function test_client_login_and_admin_access_remain_separate(): void
    {
        $client = User::factory()->create([
            'email' => 'client@example.test',
            'password' => 'StrongPassword123!',
            'is_admin' => false,
        ]);
        $admin = User::factory()->create([
            'email' => 'admin@example.test',
            'password' => 'StrongPassword123!',
            'is_admin' => true,
        ]);

        $this->post(route('client.login.store'), [
            'email' => $client->email,
            'password' => 'StrongPassword123!',
        ])->assertRedirect(route('client-portal'));
        $this->assertAuthenticatedAs($client);
        $this->get(route('admin.dashboard'))->assertForbidden();

        $this->post(route('client.logout'))->assertRedirect(route('login'));
        $this->post(route('client.login.store'), [
            'email' => $admin->email,
            'password' => 'StrongPassword123!',
        ])->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->actingAs($admin)->get(route('client-portal'))->assertForbidden();
        $this->get(route('admin.dashboard'))->assertOk();
    }
}
