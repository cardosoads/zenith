<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Inertia\Inertia;
use Inertia\Response;

class CheckoutController extends Controller
{
    public function show(): Response
    {
        $plans = Plan::query()
            ->where('is_active', true)
            ->orderBy('price_cents')
            ->get()
            ->map(function (Plan $plan) {
                return [
                    'id' => $plan->id,
                    'name' => $plan->name,
                    'price_cents' => $plan->price_cents,
                    'description' => $plan->description,
                    'popular' => $plan->slug === 'profissional',
                    'features' => $this->featuresForPlan($plan->slug),
                ];
            });

        return Inertia::render('Checkout', [
            'plans' => $plans,
        ]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function featuresForPlan(string $slug): array
    {
        return match ($slug) {
            'starter' => [
                '1 profissional',
                '1 agenda',
                'Até 100 agendamentos/mês',
                'Link de agendamento',
                'Gestão de clientes',
                'Suporte por email',
            ],
            'profissional' => [
                'Até 5 profissionais',
                'Agendas ilimitadas',
                'Agendamentos ilimitados',
                'Pagamentos integrados',
                'Relatórios avançados',
                'Suporte prioritário',
                'Personalização de marca',
            ],
            'empresa' => [
                'Profissionais ilimitados',
                'Agendas ilimitadas',
                'Agendamentos ilimitados',
                'Pagamentos integrados',
                'Relatórios completos',
                'Suporte 24/7',
                'API de integração',
                'Gerente de conta dedicado',
            ],
            default => [],
        };
    }
}
