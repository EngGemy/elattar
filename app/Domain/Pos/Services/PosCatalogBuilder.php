<?php

declare(strict_types=1);

namespace App\Domain\Pos\Services;

use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductVariant;
use App\Domain\Inventory\Models\Warehouse;
use App\Domain\Pricing\Models\Promotion;
use App\Domain\Pricing\Services\PromotionResolver;
use App\Domain\Shared\Enums\ProductStatus;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * بناء كتالوج نقطة البيع — نفس منطق المتجر مع تنسيق مناسب للكاشير.
 */
final class PosCatalogBuilder
{
    public function __construct(private PromotionResolver $promotions) {}

    /** @return array<int, array<string, mixed>> */
    public function build(?int $warehouseId = null): array
    {
        $warehouseId ??= $this->defaultWarehouseId();

        $variants = ProductVariant::query()
            ->active()
            ->with(['product.category', 'product.media', 'attributeValues.attribute'])
            ->whereHas('product', fn ($q) => $q->where('status', ProductStatus::Active))
            ->orderBy('product_id')
            ->get();

        if ($variants->isEmpty()) {
            return [];
        }

        $promoMap = $this->promotions->bestForMany($variants);

        return $variants
            ->groupBy('product_id')
            ->map(fn (Collection $group) => $this->mapProductGroup($group, $warehouseId, $promoMap))
            ->filter()
            ->sortByDesc('is_featured')
            ->sortBy('name')
            ->values()
            ->all();
    }

    /** @return array<int, array<string, mixed>> */
    public function categoriesFor(array $catalog): array
    {
        $counts = collect($catalog)->groupBy('category')->map->count();

        $cats = collect($catalog)
            ->pluck('category', 'category_id')
            ->unique()
            ->map(fn ($name, $id) => [
                'id'    => $id,
                'name'  => $name,
                'count' => $counts[$name] ?? 0,
            ])
            ->sortBy('name')
            ->values()
            ->all();

        return array_merge(
            [['id' => 0, 'name' => 'الكل', 'count' => count($catalog)]],
            $cats
        );
    }

    /** @param  Collection<int, ProductVariant>  $group
     * @param  Collection<int, Promotion|null>  $promoMap */
    private function mapProductGroup(Collection $group, int $warehouseId, Collection $promoMap): ?array
    {
        $product = $group->first()?->product;

        if (! $product) {
            return null;
        }

        $variantRows = $group
            ->map(fn (ProductVariant $v) => $this->mapVariant($v, $warehouseId, $promoMap->get($v->id)))
            ->filter()
            ->sortByDesc('is_default')
            ->values()
            ->all();

        if ($variantRows === []) {
            return null;
        }

        $default = collect($variantRows)->firstWhere('is_default', true) ?? $variantRows[0];

        return [
            'product_id'         => $product->id,
            'name'               => $product->name,
            'category'           => $product->category?->name ?? 'غير مصنّف',
            'category_id'        => $product->category_id ?? 0,
            'description'        => $product->short_description,
            'image'              => $this->productImageUrl($product, 'card'),
            'thumb'              => $this->productImageUrl($product, 'thumb'),
            'is_featured'        => (bool) $product->is_featured,
            'variant_count'      => count($variantRows),
            'default_variant_id' => $default['id'] ?? null,
            'min_price'          => collect($variantRows)->min('price') ?? 0,
            'max_price'          => collect($variantRows)->max('price') ?? 0,
            'total_stock'        => collect($variantRows)->sum('available'),
            'variants'           => $variantRows,
        ];
    }

    /** @return array<string, mixed>|null */
    private function mapVariant(ProductVariant $v, int $warehouseId, ?Promotion $promo): ?array
    {
        try {
            $attrs = $v->attributeValues->pluck('value')->implode(' / ');
            $listPrice = (int) $v->getRawOriginal('price_minor');
            $effective = $promo ? $promo->applyTo($v->price()) : $v->price();
            $salePrice = (int) $effective->minor;
            $compareRaw = $v->getRawOriginal('compare_at_price_minor');
            $onSale = $promo !== null || ($compareRaw !== null && $salePrice < $listPrice);

            return [
                'id'          => $v->id,
                'full_name'   => $v->full_name,
                'label'       => $v->unit_label ?: ($attrs ?: $v->unit->labelAr()),
                'attrs'       => $attrs,
                'sku'         => $v->sku,
                'barcode'     => $v->barcode,
                'price'       => $salePrice,
                'compare_at'  => $onSale ? $listPrice : (int) ($compareRaw ?? 0),
                'is_on_sale'  => $onSale,
                'unit'        => $v->unit->value,
                'unit_label'  => $v->unit->labelAr(),
                'step'        => (float) $v->step,
                'is_weighted' => $v->unit->isFractional(),
                'pack_label'  => $v->unit_label,
                'available'   => $v->availableAt($warehouseId),
                'is_default'  => (bool) $v->is_default,
            ];
        } catch (Throwable $e) {
            Log::warning('POS catalog: skipped variant', [
                'variant_id' => $v->id,
                'sku'        => $v->sku,
                'error'      => $e->getMessage(),
            ]);

            return null;
        }
    }

    private function productImageUrl(Product $product, string $conversion): ?string
    {
        try {
            $url = $product->getFirstMediaUrl('main', $conversion);

            return $url !== '' ? $url : null;
        } catch (Throwable) {
            return null;
        }
    }

    private function defaultWarehouseId(): int
    {
        return (int) (Warehouse::where('is_default', true)->value('id') ?? 1);
    }
}
