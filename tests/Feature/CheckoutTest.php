<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Catalog\Models\Category;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductVariant;
use App\Domain\Catalog\Models\TaxClass;
use App\Domain\Inventory\Models\StockLevel;
use App\Domain\Inventory\Models\Warehouse;
use App\Domain\Pricing\Models\Coupon;
use App\Domain\Sales\Models\Order;
use App\Domain\Shared\Enums\ProductStatus;
use App\Domain\Shared\Enums\ProductType;
use App\Domain\Shared\Enums\UnitOfMeasure;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_post_checkout_places_order_with_weighted_variant_and_coupon(): void
    {
        [$gramVariant, $pieceVariant, $couponCode] = $this->seedCheckoutCatalog();

        // محاكاة MySQL: BIGINT يُرجع كسلسلة عند القراءة الخام
        DB::table('product_variants')
            ->where('id', $gramVariant->id)
            ->update(['cost_minor' => '850']);
        DB::table('product_variants')
            ->where('id', $pieceVariant->id)
            ->update(['cost_minor' => '1200', 'price_minor' => '5000']);

        $cart = [
            (string) $gramVariant->id => [
                'variant_id'       => $gramVariant->id,
                'qty'              => 250.0,
                'name'             => 'فلفل أسود',
                'sku'              => $gramVariant->sku,
                'price_minor'      => 32000,
                'compare_at_minor' => null,
                'unit'             => 'gram',
                'unit_label'       => 'جم',
                'step'             => 50.0,
                'is_weighted'      => true,
                'image'            => null,
                'line_total_minor' => 8000,
            ],
            (string) $pieceVariant->id => [
                'variant_id'       => $pieceVariant->id,
                'qty'              => 2.0,
                'name'             => 'زيت عباد شمس',
                'sku'              => $pieceVariant->sku,
                'price_minor'      => 5000,
                'compare_at_minor' => null,
                'unit'             => 'piece',
                'unit_label'       => 'قطعة',
                'step'             => 1.0,
                'is_weighted'      => false,
                'image'            => null,
                'line_total_minor' => 10000,
            ],
        ];

        session([
            'storefront_cart'   => $cart,
            'storefront_coupon' => $couponCode,
        ]);

        Event::fake([\App\Domain\Sales\Events\OrderPlaced::class]);

        $this->get(route('storefront.checkout'));

        $response = $this->withoutMiddleware()
            ->post(route('storefront.checkout.store'), [
            'name'           => 'أحمد محمد',
            'phone'          => '01012345678',
            'city'           => 'المنصورة',
            'address'        => 'شارع الجيش — عمارة 5',
            'payment_method' => 'cod',
            'notes'          => null,
        ]);

        $order = Order::query()->with('lines')->first();

        $this->assertNotNull($order, 'Order was not created — response status: '.$response->status());
        $response->assertRedirect(route('storefront.track', $order->number));
        $response->assertSessionHas('success');

        $this->assertSame(2, $order->lines->count());
        $this->assertTrue($order->total_minor->isPositive());
        $this->assertTrue($order->discount_minor->isPositive());
        $this->assertDatabaseHas('orders', ['id' => $order->id]);
        $this->assertDatabaseHas('order_lines', ['order_id' => $order->id, 'variant_id' => $gramVariant->id]);
        $this->assertDatabaseHas('order_lines', ['order_id' => $order->id, 'variant_id' => $pieceVariant->id]);
    }

    /** @return array{0: ProductVariant, 1: ProductVariant, 2: string} */
    private function seedCheckoutCatalog(): array
    {
        $taxClass = TaxClass::create([
            'name'         => 'قياسي 14%',
            'code'         => 'VAT_TEST',
            'rate'         => 0.14,
            'is_inclusive' => true,
            'is_default'   => true,
        ]);

        $warehouse = Warehouse::create([
            'code'        => 'MAIN',
            'name'        => 'المخزن الرئيسي',
            'type'        => 'store',
            'is_default'  => true,
            'is_active'   => true,
        ]);

        $category = Category::create([
            'name'      => 'بهارات',
            'slug'      => 'baharat-test',
            'is_active' => true,
        ]);

        $spice = Product::create([
            'category_id'  => $category->id,
            'tax_class_id' => $taxClass->id,
            'sku_root'     => 'SPICE-TEST',
            'name'         => 'فلفل أسود',
            'slug'         => 'black-pepper-test',
            'type'         => ProductType::Weighted,
            'status'       => ProductStatus::Active,
        ]);

        $oil = Product::create([
            'category_id'  => $category->id,
            'tax_class_id' => $taxClass->id,
            'sku_root'     => 'OIL-TEST',
            'name'         => 'زيت عباد شمس',
            'slug'         => 'sunflower-oil-test',
            'type'         => ProductType::Simple,
            'status'       => ProductStatus::Active,
        ]);

        $gramVariant = ProductVariant::create([
            'product_id'  => $spice->id,
            'sku'         => 'SPICE-TEST-GRAM',
            'price_minor' => 32000,
            'cost_minor'  => 850,
            'unit'        => UnitOfMeasure::Gram,
            'step'        => 50,
            'is_default'  => true,
            'is_active'   => true,
        ]);

        $pieceVariant = ProductVariant::create([
            'product_id'  => $oil->id,
            'sku'         => 'OIL-TEST-1L',
            'price_minor' => 5000,
            'cost_minor'  => 1200,
            'unit'        => UnitOfMeasure::Piece,
            'step'        => 1,
            'unit_label'  => 'لتر',
            'is_default'  => true,
            'is_active'   => true,
        ]);

        foreach ([$gramVariant, $pieceVariant] as $variant) {
            StockLevel::create([
                'variant_id'   => $variant->id,
                'warehouse_id' => $warehouse->id,
                'on_hand'      => 10000,
                'reserved'     => 0,
            ]);
        }

        $couponCode = 'TEST10';

        Coupon::create([
            'code'                      => $couponCode,
            'name'                      => 'خصم اختبار',
            'type'                      => 'percent',
            'value'                     => 10,
            'min_order_minor'           => 0,
            'usage_limit_per_customer'  => 5,
            'is_active'                 => true,
        ]);

        return [$gramVariant, $pieceVariant, $couponCode];
    }
}
