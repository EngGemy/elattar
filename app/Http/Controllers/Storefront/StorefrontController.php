<?php

declare(strict_types=1);

namespace App\Http\Controllers\Storefront;

use App\Domain\Catalog\Models\Category;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductVariant;
use App\Domain\Inventory\Models\Warehouse;
use App\Domain\Pricing\Models\Promotion;
use App\Domain\Pricing\Services\PromotionResolver;
use App\Domain\Shared\ValueObjects\Money;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class StorefrontController extends Controller
{
    public function __construct(private PromotionResolver $promotions) {}

    private function defaultWarehouseId(): int
    {
        return (int) Warehouse::where('is_default', true)->value('id') ?? 1;
    }

    public function home()
    {
        $categories = Category::active()
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->get();

        $featuredProducts = Product::active()
            ->featured()
            ->with(['variants', 'defaultVariant', 'category', 'media'])
            ->orderBy('sort_order')
            ->limit(12)
            ->get();

        $promoMap = $this->promoMapForProducts($featuredProducts);

        $featured = $featuredProducts->map(fn ($p) => $this->mapProduct($p, null, $promoMap));

        $homePromotions = Promotion::active()
            ->featured()
            ->with('targets')
            ->orderBy('sort_order')
            ->limit(4)
            ->get()
            ->map(fn (Promotion $promo) => $this->mapPromotion($promo));

        return view('storefront.home', compact('categories', 'featured', 'homePromotions'));
    }

    public function catalog(Request $request)
    {
        $categories = Category::active()->whereNull('parent_id')->orderBy('sort_order')->get();

        $query = Product::active()
            ->with(['variants', 'defaultVariant', 'category', 'media']);

        if ($request->filled('category')) {
            $cat = Category::where('slug', $request->category)->first();
            if ($cat) {
                $ids = $cat->descendantsAndSelf()->pluck('id');
                $query->whereIn('category_id', $ids);
            }
        }

        if ($request->filled('q')) {
            $query->where('name', 'like', '%' . $request->q . '%');
        }

        $sort = $request->get('sort', 'newest');
        match ($sort) {
            'price_asc'  => $query->join('product_variants as pv_sort', function ($j) {
                $j->on('pv_sort.product_id', '=', 'products.id')->where('pv_sort.is_default', true);
            })->orderBy('pv_sort.price_minor')->select('products.*'),
            'price_desc' => $query->join('product_variants as pv_sort', function ($j) {
                $j->on('pv_sort.product_id', '=', 'products.id')->where('pv_sort.is_default', true);
            })->orderByDesc('pv_sort.price_minor')->select('products.*'),
            default      => $query->orderByDesc('products.created_at'),
        };

        $products = $query->paginate(24)->withQueryString();

        $warehouseId = $this->defaultWarehouseId();
        $promoMap    = $this->promoMapForProducts($products->getCollection());
        $products->getCollection()->transform(fn ($p) => $this->mapProduct($p, $warehouseId, $promoMap));

        return view('storefront.catalog', compact('categories', 'products', 'sort'));
    }

    public function product(string $slug)
    {
        $product = Product::active()
            ->with(['variants', 'category', 'taxClass', 'media'])
            ->where('slug', $slug)
            ->firstOrFail();

        $warehouseId = $this->defaultWarehouseId();
        $promoMap    = $this->promotions->bestForMany($product->variants->filter(fn ($v) => $v->is_active));

        $variants = $product->variants->filter(fn ($v) => $v->is_active)->map(function ($v) use ($warehouseId, $promoMap) {
            $row = $this->mapVariantRow($v, $warehouseId, $promoMap->get($v->id));

            return array_merge($row, [
                'sku'        => $v->sku,
                'is_default' => $v->is_default,
            ]);
        });

        $gallery   = $product->getMedia('gallery');
        $mainImage = $product->getFirstMediaUrl('main', 'card');

        return view('storefront.product', compact('product', 'variants', 'gallery', 'mainImage', 'warehouseId'));
    }

    private function mapProduct(Product $p, ?int $warehouseId = null, ?Collection $promoMap = null): array
    {
        $warehouseId ??= $this->defaultWarehouseId();

        $variants = $p->relationLoaded('variants')
            ? $p->variants->where('is_active', true)
            : $p->variants()->active()->get();

        if ($variants->isEmpty() && $p->defaultVariant) {
            $variants = collect([$p->defaultVariant]);
        }

        if ($promoMap === null) {
            $promoMap = $this->promotions->bestForMany($variants);
        }

        $variantRows = $variants
            ->map(fn ($v) => $this->mapVariantRow($v, $warehouseId, $promoMap->get($v->id)))
            ->sortByDesc('is_default')
            ->values()
            ->all();

        $default = collect($variantRows)->firstWhere('is_default', true) ?? ($variantRows[0] ?? null);

        return [
            'product_id'         => $p->id,
            'name'               => $p->name,
            'slug'               => $p->slug,
            'category'           => $p->category?->name,
            'image'              => $p->getFirstMediaUrl('main', 'card'),
            'thumb'              => $p->getFirstMediaUrl('main', 'thumb'),
            'price_minor'        => $default['price_minor'] ?? 0,
            'compare_at_minor'   => $default['compare_at_minor'] ?? null,
            'price_fmt'          => $default['price_fmt'] ?? '—',
            'compare_at_fmt'     => $default['compare_at_fmt'] ?? null,
            'is_on_sale'         => $default['is_on_sale'] ?? false,
            'sale_badge'         => $default['sale_badge'] ?? null,
            'unit'               => $default['unit'] ?? 'piece',
            'unit_label'         => $default['unit_label'] ?? 'قطعة',
            'is_weighted'        => $default['is_weighted'] ?? false,
            'available'          => $default['available'] ?? 0,
            'short_desc'         => $p->short_description,
            'is_featured'        => $p->is_featured,
            'variants'           => $variantRows,
            'variant_count'      => count($variantRows),
            'default_variant_id' => $default['id'] ?? null,
        ];
    }

    private function mapVariantRow(ProductVariant $v, int $warehouseId, ?Promotion $promo = null): array
    {
        $attrs = $v->relationLoaded('attributeValues')
            ? $v->attributeValues->pluck('value')->implode(' / ')
            : '';

        $originalMinor  = (int) $v->getRawOriginal('price_minor');
        $effective      = $promo ? $promo->applyTo($v->price()) : $v->price();
        $effectiveMinor = $effective->minor;
        $onSale         = $effectiveMinor < $originalMinor;

        return [
            'id'               => $v->id,
            'label'            => $v->unit_label ?: ($attrs ?: $v->unit->labelAr()),
            'sku'              => $v->sku,
            'price_minor'      => $effectiveMinor,
            'compare_at_minor' => $onSale ? $originalMinor : null,
            'price_fmt'        => Money::ofMinor($effectiveMinor)->format(),
            'compare_at_fmt'   => $onSale ? Money::ofMinor($originalMinor)->format() : null,
            'is_on_sale'       => $onSale,
            'sale_badge'       => $promo?->badge_text
                ?? ($onSale ? 'خصم ' . (int) round(($originalMinor - $effectiveMinor) / $originalMinor * 100) . '%' : null),
            'unit'             => $v->unit->value,
            'unit_label'       => $v->unit->labelAr(),
            'step'             => (float) $v->step,
            'is_weighted'      => $v->unit->isFractional(),
            'available'        => $v->availableAt($warehouseId),
            'is_default'       => (bool) $v->is_default,
        ];
    }

    /** @param  Collection<int, Product>  $products */
    private function promoMapForProducts(Collection $products): Collection
    {
        $variants = $products
            ->flatMap(fn (Product $p) => $p->relationLoaded('variants') ? $p->variants : collect())
            ->filter(fn ($v) => $v->is_active)
            ->unique('id')
            ->values();

        return $this->promotions->bestForMany($variants);
    }

    public function mapPromotion(Promotion $promo): array
    {
        $variants = $this->promotions->variantsFor($promo);
        $promoMap   = collect([$promo->id => $promo]);
        $warehouseId = $this->defaultWarehouseId();

        $productRows = $variants
            ->groupBy('product_id')
            ->map(function (Collection $group) use ($warehouseId, $promo) {
                $product     = $group->first()->product;
                $variantMap  = $group->mapWithKeys(fn ($v) => [$v->id => $promo]);

                return $this->mapProduct($product, $warehouseId, $variantMap);
            })
            ->values();

        return [
            'id'              => $promo->id,
            'name'            => $promo->name,
            'slug'            => $promo->slug,
            'description'     => $promo->description,
            'badge_text'      => $promo->badge_text,
            'discount_label'  => $promo->discountLabel(),
            'banner'          => $promo->getFirstMediaUrl('banner'),
            'ends_at'         => $promo->ends_at,
            'days_remaining'  => $promo->daysRemaining(),
            'show_countdown'  => $promo->daysRemaining() !== null && $promo->daysRemaining() < 7,
            'products'        => $productRows,
        ];
    }
}
