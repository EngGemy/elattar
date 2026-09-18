<?php

declare(strict_types=1);

namespace App\Support;

use App\Domain\Catalog\Models\Product;
use Illuminate\Database\Eloquent\Builder;

/** بحث المتجر — تطبيع عربي + اقتراحات سريعة */
final class ProductSearch
{
    /** @var list<string> */
    private const DIACRITICS = ['ّ', 'ً', 'ٌ', 'ٍ', 'َ', 'ُ', 'ِ', 'ْ', 'ـ', 'ٓ', 'ٔ', 'ٕ'];

    public static function normalize(?string $value): string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return '';
        }

        $value = str_replace(self::DIACRITICS, '', $value);
        $value = str_replace(['أ', 'إ', 'آ', 'ٱ'], 'ا', $value);
        $value = str_replace(['ة'], 'ه', $value);
        $value = str_replace(['ى'], 'ي', $value);
        $value = preg_replace('/\s+/u', ' ', $value) ?? $value;

        return mb_strtolower($value, 'UTF-8');
    }

    /** تعبير SQL يطبّع عمود نص عربي للمقارنة */
    public static function sqlNormalize(string $column): string
    {
        $expr = $column;
        foreach (self::DIACRITICS as $mark) {
            $expr = "REPLACE({$expr}, '{$mark}', '')";
        }
        foreach (['أ', 'إ', 'آ', 'ٱ'] as $alef) {
            $expr = "REPLACE({$expr}, '{$alef}', 'ا')";
        }
        $expr = "REPLACE({$expr}, 'ة', 'ه')";
        $expr = "REPLACE({$expr}, 'ى', 'ي')";

        return $expr;
    }

    public static function apply(Builder $query, ?string $raw): Builder
    {
        $term = self::normalize($raw);
        if ($term === '') {
            return $query;
        }

        $tokens = preg_split('/\s+/u', $term, -1, PREG_SPLIT_NO_EMPTY) ?: [$term];
        $nameNorm = self::sqlNormalize('products.name');
        $descNorm = self::sqlNormalize('products.short_description');
        $skuNorm  = self::sqlNormalize('products.sku_root');

        return $query->where(function (Builder $outer) use ($tokens, $nameNorm, $descNorm, $skuNorm) {
            foreach ($tokens as $token) {
                $like = '%' . addcslashes($token, '%_\\') . '%';
                $outer->where(function (Builder $inner) use ($like, $nameNorm, $descNorm, $skuNorm) {
                    $inner->whereRaw("{$nameNorm} LIKE ?", [$like])
                        ->orWhereRaw("{$descNorm} LIKE ?", [$like])
                        ->orWhereRaw("{$skuNorm} LIKE ?", [$like])
                        ->orWhereHas('category', function (Builder $cat) use ($like) {
                            $catNorm = ProductSearch::sqlNormalize('categories.name');
                            $cat->whereRaw("{$catNorm} LIKE ?", [$like]);
                        });
                });
            }
        });
    }

    /** @return list<array<string, mixed>> */
    public static function suggest(string $raw, int $limit = 8): array
    {
        $term = self::normalize($raw);
        if (mb_strlen($term) < 1) {
            return [];
        }

        $products = Product::active()
            ->with(['category', 'defaultVariant', 'media'])
            ->tap(fn (Builder $q) => self::apply($q, $term))
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->limit($limit)
            ->get();

        return $products->map(function (Product $p) {
            $variant = $p->defaultVariant;
            $priceMinor = $variant ? (int) $variant->getRawOriginal('price_minor') : 0;

            return [
                'id'         => $p->id,
                'name'       => $p->name,
                'slug'       => $p->slug,
                'category'   => $p->category?->name,
                'image'      => $p->getFirstMediaUrl('main', 'thumb') ?: $p->getFirstMediaUrl('main'),
                'price_fmt'  => $priceMinor > 0
                    ? \App\Domain\Shared\ValueObjects\Money::ofMinor($priceMinor)->format()
                    : null,
                'is_weighted'=> $variant?->unit?->isFractional() ?? false,
                'url'        => route('storefront.product', $p->slug),
            ];
        })->values()->all();
    }
}
