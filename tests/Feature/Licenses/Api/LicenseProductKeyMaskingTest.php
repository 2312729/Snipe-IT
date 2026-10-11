<?php

namespace Tests\Feature\Licenses\Api;

use App\Models\License;
use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class LicenseProductKeyMaskingTest extends TestCase
{
    public function test_product_key_masking(): void
    {
        $license = License::factory()->create(['serial' => 'SECRET-PRODUCT-KEY-0001']);

        $response = $this->actingAsForApi(User::factory()->viewLicenses()->create())
            ->getJson(route('api.licenses.show', $license))
            ->assertOk()
            ->assertJsonPath('product_key', License::PRODUCT_KEY_MASK);

        $this->assertStringNotContainsString('SECRET-PRODUCT-KEY-0001', $response->getContent());
    }

    public function test_product_key_is_visible_to_users_with_view_keys_permission(): void
    {
        $license = License::factory()->create(['serial' => 'SECRET-PRODUCT-KEY-0002']);

        $this->actingAsForApi(User::factory()->viewLicenses()->viewKeysLicenses()->create())
            ->getJson(route('api.licenses.show', $license))
            ->assertOk()
            ->assertJsonPath('product_key', 'SECRET-PRODUCT-KEY-0002');
    }

    public function test_product_key_is_masked_in_license_index_for_users_without_view_keys(): void
    {
        License::factory()->create(['serial' => 'SECRET-PRODUCT-KEY-0003']);
        License::factory()->create(['serial' => 'SECRET-PRODUCT-KEY-0004']);

        $response = $this->actingAsForApi(User::factory()->viewLicenses()->create())
            ->getJson(route('api.licenses.index'))
            ->assertOk()
            ->assertJson(fn (AssertableJson $json) => $json
                ->where('total', 2)
                ->where('rows.0.product_key', License::PRODUCT_KEY_MASK)
                ->where('rows.1.product_key', License::PRODUCT_KEY_MASK)
                ->etc());

        $this->assertStringNotContainsString('SECRET-PRODUCT-KEY', $response->getContent());
    }
}
