<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SubscriptionController extends Controller
{
    public function index(Request $request): Response
    {
        // Mock data based on the screenshot
        $subscription = [
            'plan_name' => 'Professional',
            'status' => 'active',
            'price' => 99.00,
            'renewal_date' => '2026-03-01',
            'usage' => [
                'professionals' => ['current' => 3, 'limit' => 5],
                'appointments' => ['current' => 145, 'limit' => null], // ilimitado
                'customers' => ['current' => 248, 'limit' => null], // ilimitado
            ],
            'payment_method' => [
                'type' => 'card',
                'last4' => '4242',
                'expiry' => '12/2028',
            ],
            'invoices' => [
                [
                    'id' => 'INV-2026-002',
                    'date' => '2026-02-01',
                    'plan' => 'Professional',
                    'amount' => 99.00,
                    'status' => 'paid',
                ],
                [
                    'id' => 'INV-2026-001',
                    'date' => '2026-01-01',
                    'plan' => 'Professional',
                    'amount' => 99.00,
                    'status' => 'paid',
                ],
            ],
        ];

        return Inertia::render('Provider/Subscription/Index', [
            'subscription' => $subscription,
        ]);
    }
}
