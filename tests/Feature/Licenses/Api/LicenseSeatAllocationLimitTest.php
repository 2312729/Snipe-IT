<?php

namespace Tests\Feature\Licenses\Api;

use App\Models\License;
use App\Models\LicenseSeat;
use App\Models\User;
use Tests\TestCase;

class LicenseSeatAllocationLimitTest extends TestCase
{
    public function test_license_seat_allocation_limits(): void
    {
        $license = License::factory()->create(['seats' => 5]);
        $actor = User::factory()->checkoutLicenses()->create();
        $holders = User::factory()->count(5)->create();

        foreach ($holders as $holder) {
            $this->actingAsForApi($actor)
                ->postJson(route('api.licenses.checkout', $license), [
                    'target_type' => 'user',
                    'assigned_to' => $holder->id,
                ])
                ->assertOk()
                ->assertStatusMessageIs('success');
        }

        $this->assertEquals(0, $license->fresh()->numRemaining());
        $this->assertEquals(5, LicenseSeat::where('license_id', $license->id)->whereIn('assigned_to', $holders->pluck('id'))->count());

        $latecomer = User::factory()->create();

        $this->actingAsForApi($actor)
            ->postJson(route('api.licenses.checkout', $license), [
                'target_type' => 'user',
                'assigned_to' => $latecomer->id,
            ])
            ->assertStatusMessageIs('error');

        $this->assertDatabaseMissing('license_seats', ['license_id' => $license->id, 'assigned_to' => $latecomer->id]);
        $this->assertEquals(5, $license->fresh()->licenseseats()->count());
    }

    public function test_checked_in_seat_can_be_allocated_to_the_next_user(): void
    {
        $license = License::factory()->create(['seats' => 2, 'reassignable' => true]);
        $actor = User::factory()->checkoutLicenses()->checkinLicenses()->create();
        [$firstSeat, $secondSeat] = $license->licenseseats()->orderBy('id')->get();
        $firstSeat->update(['assigned_to' => User::factory()->create()->id]);
        $secondSeat->update(['assigned_to' => User::factory()->create()->id]);
        $latecomer = User::factory()->create();

        $this->actingAsForApi($actor)
            ->postJson(route('api.licenses.checkin', $license), ['seat_id' => $firstSeat->id])
            ->assertStatusMessageIs('success');

        $this->assertEquals(1, $license->fresh()->numRemaining());

        $this->actingAsForApi($actor)
            ->postJson(route('api.licenses.checkout', $license), [
                'target_type' => 'user',
                'assigned_to' => $latecomer->id,
            ])
            ->assertStatusMessageIs('success');

        $this->assertEquals($latecomer->id, $firstSeat->fresh()->assigned_to);
        $this->assertEquals(0, $license->fresh()->numRemaining());
    }

    public function test_free_seat_count_in_api_reflects_allocations(): void
    {
        $license = License::factory()->create(['seats' => 5]);
        $license->licenseseats()->orderBy('id')->limit(3)->get()
            ->each(fn (LicenseSeat $seat) => $seat->update(['assigned_to' => User::factory()->create()->id]));

        $this->actingAsForApi(User::factory()->viewLicenses()->create())
            ->getJson(route('api.licenses.show', $license))
            ->assertOk()
            ->assertJson([
                'seats' => 5,
                'free_seats_count' => 2,
                'available_actions' => ['user_can_checkout' => true],
            ]);
    }
}
