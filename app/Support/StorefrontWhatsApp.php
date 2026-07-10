<?php

declare(strict_types=1);

namespace App\Support;

use App\Domain\Sales\Models\Order;

final class StorefrontWhatsApp
{
    public static function number(): string
    {
        return ShopSettings::whatsapp();
    }

    public static function url(string $message): string
    {
        return ShopSettings::whatsappUrl($message);
    }

    public static function notifyShopMessage(Order $order): string
    {
        $order->loadMissing(['lines', 'customer']);

        $addr = $order->shipping_address ?? [];
        $shop = ShopSettings::name();
        $msg  = "🌿 *طلب جديد — {$shop}*\n\n";
        $msg .= "📋 رقم الطلب: *{$order->number}*\n";
        $msg .= '👤 العميل: ' . ($order->customer?->name ?? $addr['recipient_name'] ?? '—') . "\n";
        $msg .= '📞 الهاتف: ' . ($order->customer?->phone ?? $addr['phone'] ?? '—') . "\n";

        if (! empty($addr)) {
            $msg .= '📍 العنوان: ' . trim(implode(' — ', array_filter([
                $addr['governorate'] ?? '',
                $addr['city'] ?? '',
                $addr['street'] ?? '',
            ]))) . "\n";
        }

        if (! empty($addr['payment_method'])) {
            $msg .= '💳 الدفع: ' . StorefrontCheckout::paymentLabel($addr['payment_method']);
            if ($num = StorefrontCheckout::paymentNumber($addr['payment_method'])) {
                $msg .= ' — ' . $num;
            }
            $msg .= "\n";
        }

        if ($order->notes) {
            $msg .= '📝 ملاحظات: ' . $order->notes . "\n";
        }

        $msg .= "\n🛒 *الأصناف:*\n";
        foreach ($order->lines as $i => $line) {
            $msg .= ($i + 1) . '. ' . $line->name_snapshot . ' — ' . $line->qty . ' ' . $line->unit->labelAr() . ' = ' . $line->line_total_minor->format() . "\n";
        }

        $msg .= "\n💰 *الإجمالي: {$order->total_minor->format()}*";

        return $msg;
    }

    public static function notifyShopUrl(Order $order): string
    {
        return self::url(self::notifyShopMessage($order));
    }
}
