<?php

namespace Tests\Unit;

use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Category;
use App\Models\Depreciation;
use App\Models\License;
use Tests\TestCase;

class DepreciationTest extends TestCase
{
    public function test_a_depreciation_has_models()
    {
        $depreciation = Depreciation::factory()->create();

        AssetModel::factory()
            ->count(5)
            ->create(
                [
                    'category_id' => Category::factory()->assetLaptopCategory()->create(),
                    'depreciation_id' => $depreciation->id,
                ]);

        $this->assertEquals(5, $depreciation->models->count());
    }

    public function test_depreciation_amount()
    {
        $depreciation = Depreciation::factory()->create([
            'depreciation_type' => 'amount',
            'depreciation_min' => 1000,
            'months' => 36,
        ]);

        $asset = Asset::factory()
            ->laptopMbp()
            ->create(
                [
                    'category_id' => Category::factory()->assetLaptopCategory()->create(),
                    'purchase_date' => now()->subDecade(),
                    'purchase_cost' => 4000,
                ]);
        $asset->model->update([
            'depreciation_id' => $depreciation->id,
        ]);

        $asset->getLinearDepreciatedValue();

        $this->assertEquals($depreciation->depreciation_min, $asset->getLinearDepreciatedValue());
    }

    public function test_depreciation_percentage()
    {
        $depreciation = Depreciation::factory()->create([
            'depreciation_type' => 'percent',
            'depreciation_min' => 50,
            'months' => 36,
        ]);

        $asset = Asset::factory()
            ->laptopMbp()
            ->create(
                [
                    'category_id' => Category::factory()->assetLaptopCategory()->create(),
                    'purchase_date' => now()->subDecade(),
                    'purchase_cost' => 4000,
                ]);
        $asset->model->update([
            'depreciation_id' => $depreciation->id,
        ]);

        $asset->getLinearDepreciatedValue();

        $this->assertEquals(2000, $asset->getLinearDepreciatedValue());
    }

    public function test_a_depreciation_has_licenses()
    {

        $depreciation = Depreciation::factory()->create();
        License::factory()
            ->count(5)
            ->photoshop()
            ->create(
                [
                    'category_id' => Category::factory()->licenseGraphicsCategory()->create(),
                    'depreciation_id' => $depreciation->id,
                ]);

        $this->assertEquals(5, $depreciation->licenses()->count());
    }

    /**
     * Test getLinearDepreciatedValue - ensures book value never goes negative
     * and respects depreciation_min floor
     */
    public function test_linear_depreciation_never_negative_and_respects_floor(): void
    {
        // Test with amount type - floor of 1000
        $depreciation = Depreciation::factory()->create([
            'depreciation_type' => 'amount',
            'depreciation_min' => 1000,
            'months' => 12,
        ]);

        $asset = Asset::factory()
            ->laptopMbp()
            ->create([
                'category_id' => Category::factory()->assetLaptopCategory()->create(),
                'purchase_date' => now()->subMonths(24), // 24 months ago
                'purchase_cost' => 4000,
            ]);
        $asset->model->update(['depreciation_id' => $depreciation->id]);

        $depreciatedValue = $asset->getLinearDepreciatedValue();

        // Should not go below depreciation_min (1000)
        $this->assertGreaterThanOrEqual($depreciation->depreciation_min, $depreciatedValue);
        $this->assertEquals(1000, $depreciatedValue);
    }

    /**
     * Test getLinearDepreciatedValue - percentage type with floor
     */
    public function test_linear_depreciation_percentage_never_negative(): void
    {
        $depreciation = Depreciation::factory()->create([
            'depreciation_type' => 'percent',
            'depreciation_min' => 25, // 25% floor
            'months' => 12,
        ]);

        $asset = Asset::factory()
            ->laptopMbp()
            ->create([
                'category_id' => Category::factory()->assetLaptopCategory()->create(),
                'purchase_date' => now()->subMonths(24), // 24 months ago
                'purchase_cost' => 4000,
            ]);
        $asset->model->update(['depreciation_id' => $depreciation->id]);

        $depreciatedValue = $asset->getLinearDepreciatedValue();

        // 25% of 4000 = 1000 minimum floor
        $this->assertGreaterThanOrEqual(1000, $depreciatedValue);
        $this->assertEquals(1000, $depreciatedValue);
    }

    /**
     * Test getLinearDepreciatedValue - book value does not go negative when no floor
     */
    public function test_linear_depreciation_no_floor_does_not_go_negative(): void
    {
        $depreciation = Depreciation::factory()->create([
            'depreciation_type' => 'amount',
            'depreciation_min' => 0, // No floor
            'months' => 12,
        ]);

        $asset = Asset::factory()
            ->laptopMbp()
            ->create([
                'category_id' => Category::factory()->assetLaptopCategory()->create(),
                'purchase_date' => now()->subMonths(24), // 24 months ago
                'purchase_cost' => 4000,
            ]);
        $asset->model->update(['depreciation_id' => $depreciation->id]);

        $depreciatedValue = $asset->getLinearDepreciatedValue();

        // Should be 0, not negative
        $this->assertEquals(0, $depreciatedValue);
        $this->assertGreaterThanOrEqual(0, $depreciatedValue);
    }

    /**
     * Test getLinearDepreciatedValue - early in depreciation period
     */
    public function test_linear_depreciation_early_period_calculates_correctly(): void
    {
        $depreciation = Depreciation::factory()->create([
            'depreciation_type' => 'amount',
            'depreciation_min' => 0,
            'months' => 24,
        ]);

        $asset = Asset::factory()
            ->laptopMbp()
            ->create([
                'category_id' => Category::factory()->assetLaptopCategory()->create(),
                'purchase_date' => now()->subMonths(6), // 6 months ago
                'purchase_cost' => 2400,
            ]);
        $asset->model->update(['depreciation_id' => $depreciation->id]);

        $depreciatedValue = $asset->getLinearDepreciatedValue();

        // 6/24 = 25% depreciated, so 75% remaining = 1800
        $this->assertEquals(1800, $depreciatedValue);
    }
}
