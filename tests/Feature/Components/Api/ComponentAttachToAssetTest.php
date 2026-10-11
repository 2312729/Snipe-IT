<?php

namespace Tests\Feature\Components\Api;

use App\Models\Asset;
use App\Models\Component;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ComponentAttachToAssetTest extends TestCase
{
    public function test_component_attached_to_hardware(): void
    {
        $ram = Component::factory()->ramCrucial8()->create(['name' => 'Crucial 16GB DDR4 RAM', 'qty' => 10]);
        $laptop = Asset::factory()->laptopMbp()->create(['asset_tag' => 'LAP-001']);

        $this->actingAsForApi(User::factory()->checkoutComponents()->create())
            ->postJson(route('api.components.checkout', $ram->id), [
                'assigned_to' => $laptop->id,
                'assigned_qty' => 2,
            ])
            ->assertOk()
            ->assertStatusMessageIs('success');

        $this->assertDatabaseHas('components_assets', [
            'component_id' => $ram->id,
            'asset_id' => $laptop->id,
            'assigned_qty' => 2,
        ]);
        $this->assertEquals(8, $ram->fresh()->numRemaining());
    }

    public function test_full_checkin_detaches_component_and_restores_quantity(): void
    {
        $ram = Component::factory()->ramCrucial8()->create(['qty' => 10]);
        $laptop = Asset::factory()->laptopMbp()->create();
        $pivotId = $this->attach($ram, $laptop, 2);

        $this->actingAsForApi(User::factory()->checkinComponents()->create())
            ->postJson(route('api.components.checkin', $pivotId), ['checkin_qty' => 2])
            ->assertOk()
            ->assertStatusMessageIs('success');

        $this->assertDatabaseMissing('components_assets', ['id' => $pivotId]);
        $this->assertEquals(10, $ram->fresh()->numRemaining());
    }

    public function test_partial_checkin_keeps_remaining_components_attached(): void
    {
        $ram = Component::factory()->ramCrucial8()->create(['qty' => 10]);
        $laptop = Asset::factory()->laptopMbp()->create();
        $pivotId = $this->attach($ram, $laptop, 3);

        $this->actingAsForApi(User::factory()->checkinComponents()->create())
            ->postJson(route('api.components.checkin', $pivotId), ['checkin_qty' => 1])
            ->assertOk()
            ->assertStatusMessageIs('success');

        $this->assertDatabaseHas('components_assets', ['id' => $pivotId, 'asset_id' => $laptop->id, 'assigned_qty' => 2]);
        $this->assertEquals(8, $ram->fresh()->numRemaining());
    }

    /**
     * Attach a component to an asset the same way the checkout API does and return the pivot id.
     */
    private function attach(Component $component, Asset $asset, int $quantity): int
    {
        $this->actingAsForApi(User::factory()->checkoutComponents()->create())
            ->postJson(route('api.components.checkout', $component->id), [
                'assigned_to' => $asset->id,
                'assigned_qty' => $quantity,
            ])
            ->assertStatusMessageIs('success');

        return DB::table('components_assets')
            ->where('component_id', $component->id)
            ->where('asset_id', $asset->id)
            ->value('id');
    }
}
