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
        $userA = User::factory()->create(['is_admin' => true]);
        $userB = User::factory()->create(['is_admin' => false]);

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
                'email' => 'user-a@example.test',
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

        $this->actingAs($userA)
            ->post(route('admin.inquiries.accept', $inquiry))
            ->assertRedirect();

        $case = CaseRecord::query()->where('inquiry_id', $inquiry->id)->firstOrFail();

        $read = $this->actingAs($userB)->post(route('client-portal.lookup'), [
            'email' => $case->client_email,
            'reference_no' => $case->reference_no,
        ]);

        $update = $this->actingAs($userB)->patch(route('admin.cases.update', $case), [
            'status' => 'closed',
        ]);

        $delete = $this->actingAs($userB)->delete('/admin/cases/'.$case->id);

        // Required security contract: every cross-user operation must be hidden
        // or forbidden. This currently exposes gaps in the app's ownership model.
        $this->assertSame([403, 403, 404], [
            $read->getStatusCode(),
            $update->getStatusCode(),
            $delete->getStatusCode(),
        ]);
    }
}
