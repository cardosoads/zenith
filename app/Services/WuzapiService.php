<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class WuzapiService
{
    public function __construct(
        private readonly string $baseUrl,
        private readonly string $userToken,
    ) {}

    public static function make(): self
    {
        return new self(
            baseUrl: config('services.wuzapi.base_url'),
            userToken: config('services.wuzapi.user_token'),
        );
    }

    public function isReady(): bool
    {
        try {
            $response = $this->request()->get('/session/status')->json();

            return ($response['data']['Connected'] ?? false) && ($response['data']['LoggedIn'] ?? false);
        } catch (\Throwable) {
            return false;
        }
    }

    public function sendText(string $phone, string $body, ?string $messageId = null): array
    {
        $payload = ['Phone' => $phone, 'Body' => $body];

        if ($messageId) {
            $payload['Id'] = $messageId;
        }

        return $this->request()->post('/chat/send/text', $payload)->json();
    }

    public function isOnWhatsApp(string $phone): bool
    {
        $response = $this->request()->post('/user/check', ['Phone' => [$phone]])->json();

        return $response['data']['Users'][0]['IsInWhatsapp'] ?? false;
    }

    public function getQrCode(): ?string
    {
        return $this->request()->get('/session/qr')->json('data.QRCode');
    }

    public function connect(): array
    {
        return $this->request()
            ->post('/session/connect', ['Subscribe' => ['Message', 'ReadReceipt'], 'Immediate' => false])
            ->json();
    }

    private function request(): PendingRequest
    {
        return Http::baseUrl($this->baseUrl)
            ->timeout(15)
            ->withHeaders(['Authorization' => $this->userToken]);
    }
}
