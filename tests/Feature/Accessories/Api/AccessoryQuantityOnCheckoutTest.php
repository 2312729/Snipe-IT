<?php

namespace Tests\Feature\Accessories\Api;

use App\Models\Accessory;
use App\Models\User;
use Tests\TestCase;

class AccessoryQuantityOnCheckoutTest extends TestCase
{
    public function test_accessory_quantity_adjusts_on_checkout(): void
    {
        $accessory = Accessory::factory()->create(['qty' => 10]);
        $user = User::factory()->create();

        $this->assertEquals(10, $accessory->numRemaining());

        $this->actingAsForApi(User::factory()->checkoutAccessories()->create())
            ->postJson(route('api.accessories.checkout', $accessory), [
                'checkout_to_type' => 'user',
                'assigned_user' => $user->id,
                'checkout_qty' => 2,
            ])
            ->assertOk()
            ->assertStatusMessageIs('success');

        $accessory->refresh();
        $this->assertEquals(10, $accessory->qty);
        $this->assertEquals(2, $accessory->checkouts()->where('assigned_to', $user->id)->count());
        $this->assertEquals(8, $accessory->numRemaining());
    }

    public function test_checkin_restores_remaining_quantity(): void
    {
        $accessory = Accessory::factory()->create(['qty' => 10]);
        $actor = User::factory()->checkoutAccessories()->checkinAccessories()->create();

        $this->actingAsForApi($actor)
            ->postJson(route('api.accessories.checkout', $accessory), [
                'checkout_to_type' => 'user',
                'assigned_user' => User::factory()->create()->id,
                'checkout_qty' => 2,
            ])
            ->assertStatusMessageIs('success');

        foreach ($accessory->checkouts()->get() as $index => $checkout) {
            $this->actingAsForApi($actor)
                ->postJson(route('api.accessories.checkin', $checkout->id))
                ->assertOk()
                ->assertStatusMessageIs('success');

            $this->assertEquals(9 + $index, $accessory->fresh()->numRemaining());
        }

        $this->assertEquals(0, $accessory->checkouts()->count());
    }

    public function test_cannot_checkout_more_than_remaining_quantity(): void
    {
        $accessory = Accessory::factory()->create(['qty' => 3]);

        $this->actingAsForApi(User::factory()->checkoutAccessories()->create())
            ->postJson(route('api.accessories.checkout', $accessory), [
                'checkout_to_type' => 'user',
                'assigned_user' => User::factory()->create()->id,
                'checkout_qty' => 4,
            ])
            ->assertOk()
            ->assertStatusMessageIs('error');

        $this->assertEquals(0, $accessory->checkouts()->count());
        $this->assertEquals(3, $accessory->fresh()->numRemaining());
    }
}
