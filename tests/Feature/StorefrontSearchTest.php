<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Catalog\Models\Category;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductVariant;
use App\Domain\Catalog\Models\TaxClass;
use App\Domain\Shared\Enums\ProductStatus;
use App\Domain\Shared\Enums\ProductType;
use App\Domain\Shared\Enums\UnitOfMeasure;
use App\Support\ProductSearch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_search_finds_product_without_diacritics(): void
    {
        $this->seedSpice('كمّون');

        $this->get(route('storefront.catalog', ['q' => 'كمون']))
            ->assertOk()
            ->assertSee('كمّون', false);
    }

    public function test_suggest_returns_matching_products(): void
    {
        $this->seedSpice('فلفل أسود');
        $this->seedSpice('كمّون');

        $response = $this->getJson(route('storefront.catalog.suggest', ['q' => 'فلف']));

        $response->assertOk()
            ->assertJsonPath('q', 'فلف')
            ->assertJsonCount(1, 'items')
            ->assertJsonPath('items.0.name', 'فلفل أسود');
    }

    public function test_normalize_strips_arabic_marks(): void
    {
        $this->assertSame('كمون', ProductSearch::normalize('كمّون'));
        $this->assertSame('فلفل اسود', ProductSearch::normalize('فلفل أسود'));
    }

    private function seedSpice(string $name): Product
    {
        $tax = TaxClass::query()->first() ?? TaxClass::create([
            'name' => 'قياسي', 'code' => 'VAT', 'rate' => 0.14, 'is_inclusive' => true, 'is_default' => true,
        ]);

        $cat = Category::query()->first() ?? Category::create([
            'name' => 'بهارات', 'slug' => 'spices', 'is_active' => true, 'sort_order' => 1,
        ]);

        $product = Product::create([
            'category_id'  => $cat->id,
            'tax_class_id' => $tax->id,
            'sku_root'     => 'SP-' . uniqid(),
            'name'         => $name,
            'type'         => ProductType::Simple,
            'status'       => ProductStatus::Active,
            'has_variants' => false,
            'is_featured'  => true,
            'sort_order'   => 1,
        ]);

        ProductVariant::create([
            'product_id'   => $product->id,
            'sku'          => $product->sku_root . '-G',
            'unit'         => UnitOfMeasure::Gram,
            'step'         => 1,
            'price_minor'  => 18000,
            'cost_minor'   => 10000,
            'is_default'   => true,
            'is_active'    => true,
        ]);

        return $product;
    }
}
