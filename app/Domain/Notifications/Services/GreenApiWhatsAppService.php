<?php

declare(strict_types=1);

namespace App\Domain\Notifications\Services;

use App\Domain\Notifications\Contracts\WhatsAppServiceInterface;
use App\Support\PhoneNumber;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * إرسال واتساب عبر Green API (غير Meta Cloud API).
 *
 * @see https://green-api.com/en/docs/api/sending/SendMessage/
 */
final class GreenApiWhatsAppService implements WhatsAppServiceInterface
{
    public function __construct(
        private readonly string $baseUrl,
        private readonly string $instanceId,
        private readonly string $token,
        private readonly int $timeoutSeconds = 15,
    ) {}

    public function isConfigured(): bool
    {
        return $this->instanceId !== '' && $this->token !== '';
    }

    public function sendText(string $e164Phone, string $message): void
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('Green API WhatsApp is not configured.');
        }

        $chatDigits = PhoneNumber::digitsOnly($e164Phone);

        if ($chatDigits === null) {
            throw new RuntimeException("Invalid WhatsApp recipient phone: {$e164Phone}");
        }

        $url = rtrim($this->baseUrl, '/')
            . '/waInstance' . $this->instanceId
            . '/sendMessage/' . $this->token;

        try {
            $response = Http::timeout($this->timeoutSeconds)
                ->acceptJson()
                ->asJson()
                ->post($url, [
                    'chatId'  => $chatDigits . '@c.us',
                    'message' => $message,
                ]);
        } catch (ConnectionException $e) {
            Log::warning('whatsapp.green_api.connection_failed', [
                'error' => $e->getMessage(),
            ]);

            throw new RuntimeException('WhatsApp gateway connection failed: ' . $e->getMessage(), 0, $e);
        }

        if (! $response->successful()) {
            Log::error('whatsapp.green_api.send_failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
                'to'     => $chatDigits,
            ]);

            throw new RuntimeException(
                'WhatsApp gateway returned HTTP ' . $response->status() . ': ' . $response->body()
            );
        }

        Log::info('whatsapp.green_api.sent', [
            'to'      => $chatDigits,
            'idMessage' => $response->json('idMessage'),
        ]);
    }
}
