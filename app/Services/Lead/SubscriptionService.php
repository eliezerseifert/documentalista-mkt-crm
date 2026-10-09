<?php

namespace App\Services\Lead;

use App\Enums\LeadEventType;
use App\Enums\LeadStatus;
use App\Models\Lead;
use App\Models\LeadEvent;
use App\Models\Subscription;
use App\Services\Automation\FlowEngineService;
use Carbon\Carbon;

class SubscriptionService
{
    public function __construct(
        protected FlowEngineService $flowEngine,
    ) {}

    /**
     * Registra a conversão de assinatura de um lead.
     */
    public function recordConversion(Lead $lead, array $data): Subscription
    {
        $planName = $data['plan_name'] ?? $data['plan'] ?? 'Plano Pro';
        $amount = (float) ($data['amount'] ?? $data['price'] ?? 0.00);
        $transactionId = $data['transaction_id'] ?? $data['id'] ?? null;
        $subscribedAt = isset($data['subscribed_at']) 
            ? Carbon::parse($data['subscribed_at']) 
            : now();

        $subscription = Subscription::create([
            'lead_id' => $lead->id,
            'plan_name' => $planName,
            'amount' => $amount,
            'transaction_id' => $transactionId,
            'subscribed_at' => $subscribedAt,
            'payload' => $data,
        ]);

        // Atualiza status do Lead para Assinou
        $lead->update([
            'status' => LeadStatus::Assinou,
        ]);

        // Cancela todos os fluxos ativos imediatamente
        $this->flowEngine->cancelActiveFlows($lead, 'Assinatura confirmada no checkout');

        // Registra evento de conversão
        LeadEvent::create([
            'lead_id' => $lead->id,
            'event_type' => LeadEventType::Assinou,
            'title' => '🎉 Assinatura Concluída com Sucesso!',
            'description' => "Plano: {$planName} | Valor: R$ " . number_format($amount, 2, ',', '.') . " | Transação: {$transactionId}",
            'metadata' => [
                'subscription_id' => $subscription->id,
                'plan_name' => $planName,
                'amount' => $amount,
                'transaction_id' => $transactionId,
            ],
            'score_delta' => 20,
            'occurred_at' => $subscribedAt,
        ]);

        $lead->increment('score', 20);

        return $subscription;
    }
}
