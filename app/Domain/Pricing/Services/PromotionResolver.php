<?php

declare(strict_types=1);

namespace App\Domain\Pricing\Services;

use App\Domain\Catalog\Models\ProductVariant;
use App\Domain\Pricing\Models\Promotion;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

final class PromotionResolver
{
    /** @var Collection<int, Promotion>|null */
    private ?Collection $activePromotions = null;

    public function bestFor(ProductVariant $variant): ?Promotion
    {
        $variant->loadMissing('product.category');

        $applicable = $this->activePromotions()
            ->filter(fn (Promotion $promo) => $promo->appliesTo($variant));

        if ($applicable->isEmpty()) {
            return null;
        }

        return $this->pickBest($applicable, $variant);
    }

    /**
     * @param  Collection<int, ProductVariant>  $variants
     * @return Collection<int, Promotion|null>
     */
    public function bestForMany(Collection $variants): Collection
    {
        if ($variants->isEmpty()) {
            return collect();
        }

        $variants->each(fn (ProductVariant $v) => $v->loadMissing('product.category'));
        $promotions = $this->activePromotions();

        return $variants->mapWithKeys(function (ProductVariant $variant) use ($promotions) {
            $applicable = $promotions->filter(fn (Promotion $promo) => $promo->appliesTo($variant));

            return [$variant->id => $applicable->isEmpty() ? null : $this->pickBest($applicable, $variant)];
        });
    }

    /** @return Collection<int, ProductVariant> */
    public function variantsFor(Promotion $promotion): Collection
    {
        $promotion->loadMissing('targets');

        return ProductVariant::query()
            ->active()
            ->with(['product.category', 'attributeValues.attribute'])
            ->get()
            ->filter(fn (ProductVariant $variant) => $promotion->appliesTo($variant))
            ->values();
    }

    /** @return Collection<int, Promotion> */
    private function activePromotions(): Collection
    {
        if ($this->activePromotions !== null) {
            return $this->activePromotions;
        }

        $this->activePromotions = Cache::remember(
            'promotions.active',
            300,
            fn () => Promotion::active()
                ->with('targets')
                ->orderByDesc('priority')
                ->get()
        );

        return $this->activePromotions;
    }

    /** @param  Collection<int, Promotion>  $promotions */
    private function pickBest(Collection $promotions, ProductVariant $variant): Promotion
    {
        $original = $variant->price();

        return $promotions
            ->sortBy(fn (Promotion $promo) => [
                -$promo->priority,
                $promo->applyTo($original)->minor,
            ])
            ->first();
    }
}
