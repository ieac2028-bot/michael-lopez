<?php

namespace Tests\Feature;

use App\Models\Attorney;
use App\Models\CaseRecord;
use App\Models\Inquiry;
use App\Models\PracticeArea;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CaseOwnershipTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_b_cannot_read_update_or_delete_user_a_case(): void
    {
        $userA = User::factory()->create([
            'email' => 'user-a@example.test',
            'is_admin' => false,
        ]);
        $userB = User::factory()->create([
            'email' => 'user-b@example.test',
            'is_admin' => false,
        ]);
        $admin = User::factory()->create(['is_admin' => true]);

        $area = PracticeArea::create([
            'name' => 'Commercial Law',
            'slug' => 'commercial-law-test',
            'description' => 'Feature test practice area.',
        ]);

        Attorney::create([
            'name' => 'Test Attorney',
            'rank' => 'Partner',
            'practice_area_id' => $area->id,
            'is_available' => true,
            'coverage_areas' => ['All Philippines'],
        ]);

        $this->actingAs($userA)
            ->post(route('consultation.store'), [
                'full_name' => 'User A',
                'email' => $userA->email,
                'phone' => '09123456789',
                'client_type' => 'individual',
                'client_location' => 'Makati',
                'practice_area_id' => $area->id,
                'legal_profile' => 'Test profile',
                'client_requirements' => 'Test requirements',
                'preferred_date' => now()->addDay()->toDateString(),
                'preferred_time' => 'Morning (9:00 AM – 12:00 PM)',
                'message' => 'User A test matter',
            ])
            ->assertRedirect();

        $inquiry = Inquiry::query()->latest('id')->firstOrFail();

        $this->actingAs($admin)
            ->post(route('admin.inquiries.accept', $inquiry))
            ->assertRedirect();

        $case = CaseRecord::query()->where('inquiry_id', $inquiry->id)->firstOrFail();

        $ownerRead = $this->actingAs($userA)->post(route('client-portal.lookup'), [
            'reference_no' => $case->reference_no,
        ]);
        $ownerRead->assertOk()->assertSee('User A test matter');

        $crossUserRead = $this->actingAs($userB)->post(route('client-portal.lookup'), [
            'reference_no' => $case->reference_no,
        ]);
        $forgedEmailRead = $this->actingAs($userB)->post(route('client-portal.lookup'), [
            'email' => $userA->email,
            'reference_no' => $case->reference_no,
        ]);

        $crossUserUpdate = $this->actingAs($userB)->patch(route('admin.cases.update', $case), [
            'status' => 'closed',
        ]);

        $crossUserDelete = $this->actingAs($userB)->delete(route('admin.cases.destroy', $case));

        $this->assertSame([404, 403, 403], [
            $crossUserRead->getStatusCode(),
            $crossUserUpdate->getStatusCode(),
            $crossUserDelete->getStatusCode(),
        ]);
        $forgedEmailRead->assertUnprocessable();
        $crossUserRead->assertNotFound()->assertSee('No match found')->assertDontSee('User A test matter');
        $this->assertDatabaseHas('case_records', ['id' => $case->id, 'status' => 'active']);
    }
}
