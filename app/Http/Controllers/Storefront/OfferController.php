<?php

declare(strict_types=1);

namespace App\Http\Controllers\Storefront;

use App\Domain\Pricing\Models\Promotion;
use App\Http\Controllers\Controller;

class OfferController extends Controller
{
    public function __construct(
        private StorefrontController $storefront,
    ) {}

    public function index()
    {
        $promotions = Promotion::active()
            ->with('targets')
            ->orderBy('sort_order')
            ->get()
            ->map(fn (Promotion $promo) => $this->storefront->mapPromotion($promo));

        return view('storefront.offers', compact('promotions'));
    }
}
