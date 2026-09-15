<?php

declare(strict_types=1);

namespace App\Domain\Notifications\Contracts;

interface WhatsAppServiceInterface
{
    /**
     * إرسال رسالة نصية إلى رقم بصيغة E.164 (+20…).
     *
     * @throws \RuntimeException عند فشل الإرسال بعد معالجة المزود
     */
    public function sendText(string $e164Phone, string $message): void;

    public function isConfigured(): bool;
}
