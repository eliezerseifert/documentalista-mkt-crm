<?php

namespace App\Services\WhatsApp;

use App\Enums\LeadEventType;
use App\Models\Lead;
use App\Models\LeadEvent;

class WhatsAppOptOutService
{
    protected array $optOutKeywords = [
        'sair',
        'parar',
        'cancelar',
        'stop',
        'descadastrar',
        'nao quero',
        'não quero',
        'pare',
        'bloquear',
    ];

    /**
     * Verifica se o texto recebido indica intenção de opt-out.
     */
    public function isOptOutRequest(string $text): bool
    {
        $normalized = trim(mb_strtolower($text));

        foreach ($this->optOutKeywords as $keyword) {
            if ($normalized === $keyword || str_starts_with($normalized, $keyword . ' ')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Processa a solicitação de opt-out no lead.
     */
    public function processOptOut(Lead $lead, string $rawMessage): void
    {
        $lead->update([
            'is_whatsapp_opted_out' => true,
        ]);

        LeadEvent::create([
            'lead_id' => $lead->id,
            'event_type' => LeadEventType::OptOut,
            'channel' => 'whatsapp',
            'title' => 'Opt-out do WhatsApp solicitado pelo lead',
            'description' => "Mensagem recebida: \"{$rawMessage}\". Envios automáticos por WhatsApp interrompidos.",
            'occurred_at' => now(),
        ]);
    }
}
