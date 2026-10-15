<?php

namespace Tests\Feature;

use App\Models\Attorney;
use App\Models\CaseRecord;
use App\Models\PracticeArea;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class AttorneyAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_issue_an_individual_attorney_login_link(): void
    {
        Notification::fake();
        $admin = User::factory()->create(['is_admin' => true]);
        $attorney = $this->attorney('Atty. One');

        $this->actingAs($admin)->post(route('admin.attorneys.account.store', $attorney), [
            'email' => 'attorney@example.test',
        ])->assertRedirect();

        $account = User::where('email', 'attorney@example.test')->firstOrFail();
        $this->assertSame('attorney', $account->role);
        $this->assertSame($attorney->id, $account->attorney_id);
        $this->assertFalse($account->is_admin);
        Notification::assertSentTo($account, ResetPassword::class);
    }

    public function test_attorney_login_is_separate_and_cases_are_scoped_to_the_linked_profile(): void
    {
        $area = PracticeArea::create(['name' => 'Property', 'slug' => 'property-auth-test', 'description' => 'Test area.']);
        $assigned = Attorney::create(['name' => 'Atty. Assigned', 'rank' => 'Partner', 'practice_area_id' => $area->id]);
        $other = Attorney::create(['name' => 'Atty. Other', 'rank' => 'Partner', 'practice_area_id' => $area->id]);
        $user = User::factory()->create(['email' => 'attorney@example.test', 'is_admin' => false]);
        $user->forceFill(['role' => 'attorney', 'attorney_id' => $assigned->id])->save();
        $ownCase = $this->caseFor($assigned, 'SAFA-OWN-001');
        $otherCase = $this->caseFor($other, 'SAFA-OTHER-001');

        $this->get(route('attorney.login'))->assertOk()->assertSee('Attorney sign in');
        $this->post(route('client.login.store'), ['email' => $user->email, 'password' => 'password'])->assertSessionHasErrors('email');

        $this->post(route('attorney.login.store'), ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect(route('attorney.dashboard'));
        $this->assertAuthenticatedAs($user);
        $this->get(route('client-portal'))->assertForbidden();
        $this->get(route('admin.dashboard'))->assertForbidden();
        $this->get(route('attorney.dashboard'))->assertOk()->assertSee($ownCase->reference_no)->assertDontSee($otherCase->reference_no);
        $this->patch(route('attorney.cases.update', $ownCase), ['status' => 'on_hold'])->assertRedirect();
        $this->patch(route('attorney.cases.update', $otherCase), ['status' => 'closed'])->assertNotFound();
        $this->assertDatabaseHas('case_records', ['id' => $ownCase->id, 'status' => 'on_hold']);
        $this->assertDatabaseHas('case_records', ['id' => $otherCase->id, 'status' => 'active']);
    }

    public function test_password_reset_link_changes_password_and_verifies_email(): void
    {
        $user = User::factory()->unverified()->create(['email' => 'reset@example.test']);
        $token = Password::createToken($user);

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'NewStrongPassword123!',
            'password_confirmation' => 'NewStrongPassword123!',
        ])->assertRedirect(route('login'));

        $user->refresh();
        $this->assertTrue(Hash::check('NewStrongPassword123!', $user->password));
        $this->assertNotNull($user->email_verified_at);
    }

    private function attorney(string $name): Attorney
    {
        return Attorney::create(['name' => $name, 'rank' => 'Partner']);
    }

    private function caseFor(Attorney $attorney, string $reference): CaseRecord
    {
        return CaseRecord::create([
            'reference_no' => $reference,
            'client_email' => 'client@example.test',
            'title' => 'Sample matter',
            'attorney_id' => $attorney->id,
            'timeline' => [['label' => 'Matter in progress', 'date' => now()->toDateString(), 'state' => 'current']],
            'status' => 'active',
        ]);
    }
}
