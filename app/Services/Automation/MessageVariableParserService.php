<?php

namespace App\Services\Automation;

use App\Models\FlowStep;
use App\Models\Lead;
use App\Models\TrackedLink;
use Illuminate\Support\Str;

class MessageVariableParserService
{
    /**
     * Substitui variáveis dinâmicas no texto e converte links em URLs rastreáveis.
     */
    public function parse(string $template, Lead $lead, ?FlowStep $step = null, ?string $fallbackCheckout = 'https://checkout.v2.e-flips.com.br'): string
    {
        $coupon = $step?->promo_coupon_code ?? 'DOC10';

        // Gera ou busca link rastreado para o checkout
        $trackedCheckoutUrl = $this->createOrGetTrackedLink($lead, $step, $fallbackCheckout);

        $replacements = [
            '{{nome}}' => $lead->name,
            '{{primeiro_nome}}' => $lead->first_name,
            '{{email}}' => $lead->email,
            '{{dias_restantes_trial}}' => (string) $lead->days_remaining_trial,
            '{{link_checkout}}' => $trackedCheckoutUrl,
            '{{cupom}}' => $coupon,
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }

    /**
     * Cria ou reaproveita um link rastreável para o lead e etapa.
     */
    public function createOrGetTrackedLink(Lead $lead, ?FlowStep $step, string $destinationUrl): string
    {
        // Anexa parâmetros UTM e identificadores ao link de checkout se for da e-flips
        $separator = str_contains($destinationUrl, '?') ? '&' : '?';
        $fullDestination = $destinationUrl;

        if (str_contains($destinationUrl, 'checkout.v2.e-flips.com.br')) {
            $params = http_build_query(array_filter([
                'lead_id' => $lead->id,
                'email' => $lead->email,
                'name' => $lead->name,
                'phone' => $lead->phone,
                'utm_source' => $lead->utm_source ?? 'crm_automacao',
                'utm_medium' => $step ? 'step_' . $step->step_number : 'automacao',
                'utm_campaign' => $step?->flow?->slug ?? 'ciclo_30_dias',
            ]));

            $fullDestination = $destinationUrl . $separator . $params;
        }

        $tracked = TrackedLink::firstOrCreate(
            [
                'lead_id' => $lead->id,
                'flow_step_id' => $step?->id,
                'destination_url' => $fullDestination,
            ],
            [
                'token' => Str::random(40),
            ]
        );

        return url('/t/click/' . $tracked->token);
    }
}
