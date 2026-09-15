<?php

declare(strict_types=1);

namespace App\Domain\Notifications\Services;

use App\Domain\Notifications\Contracts\WhatsAppServiceInterface;
use Illuminate\Support\Facades\Log;

/** مزود صامت عند تعطيل الإشعارات أو غياب بيانات الاعتماد — لا يفشل الطلب. */
final class NullWhatsAppService implements WhatsAppServiceInterface
{
    public function isConfigured(): bool
    {
        return false;
    }

    public function sendText(string $e164Phone, string $message): void
    {
        Log::debug('whatsapp.null.skipped', [
            'to'      => $e164Phone,
            'preview' => mb_substr($message, 0, 80),
        ]);
    }
}
