<?php

namespace App\Contracts;

use App\Models\Booking;

interface PixProviderInterface
{
    /**
     * @return array{external_id:string,qr_code_text:string,qr_code_image_url:string,status:string}
     */
    public function createCharge(Booking $booking): array;

    /**
     * @param  array<string, mixed>  $payload
     * @return array{external_id:string,status:string}
     */
    public function parseWebhookPayload(array $payload): array;
}
