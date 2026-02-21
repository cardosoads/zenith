<?php

namespace App\Services\Pix;

use App\Contracts\PixProviderInterface;
use App\Models\Booking;
use Illuminate\Support\Str;

class FakePixProvider implements PixProviderInterface
{
    public function createCharge(Booking $booking): array
    {
        $externalId = 'pix_'.Str::uuid()->toString();

        return [
            'external_id' => $externalId,
            'qr_code_text' => '00020101021226810014br.gov.bcb.pix2563qrcodes-pix.example/'.$externalId,
            'qr_code_image_url' => 'https://api.qrserver.com/v1/create-qr-code/?size=250x250&data='.$externalId,
            'status' => 'awaiting_payment',
        ];
    }

    public function parseWebhookPayload(array $payload): array
    {
        return [
            'external_id' => (string) ($payload['external_id'] ?? ''),
            'status' => (string) ($payload['status'] ?? 'pending'),
        ];
    }
}
