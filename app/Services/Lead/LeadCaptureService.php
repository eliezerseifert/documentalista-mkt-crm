<?php

namespace App\Services\Lead;

use App\Enums\LeadEventType;
use App\Enums\LeadStatus;
use App\Models\Lead;
use App\Models\LeadEvent;
use App\Models\WebhookLog;
use App\Services\Automation\FlowEngineService;
use Carbon\Carbon;

class LeadCaptureService
{
    public function __construct(
        protected PhoneNormalizerService $phoneNormalizer,
        protected FlowEngineService $flowEngine,
    ) {}

    /**
     * Processa a captura de um lead vindo do formulário de registro.
     */
    public function process(array $data, ?string $ip = null, ?array $headers = null): Lead
    {
        $email = strtolower(trim($data['email']));
        $rawPhone = $data['phone'] ?? $data['whatsapp'] ?? '';
        $normalizedPhone = $this->phoneNormalizer->normalize($rawPhone);

        // Deduplicação: busca por e-mail primeiro, depois por telefone
        $lead = Lead::where('email', $email)
            ->orWhere('phone', $normalizedPhone)
            ->first();

        $registeredAt = isset($data['registered_at']) 
            ? Carbon::parse($data['registered_at']) 
            : now();

        $trialEndsAt = $registeredAt->copy()->addDays(15);
        $cycleEndsAt = $registeredAt->copy()->addDays(30);

        $isNew = false;

        if (!$lead) {
            $isNew = true;
            $lead = new Lead();
            $lead->status = LeadStatus::Novo;
            $lead->trial_ends_at = $trialEndsAt;
            $lead->cycle_ends_at = $cycleEndsAt;
        }

        $lead->name = trim($data['name']);
        $lead->email = $email;
        $lead->phone = $normalizedPhone;
        $lead->saas_user_id = $data['saas_user_id'] ?? $data['user_id'] ?? $lead->saas_user_id;

        // UTMs
        $lead->utm_source = $data['utm_source'] ?? $lead->utm_source;
        $lead->utm_medium = $data['utm_medium'] ?? $lead->utm_medium;
        $lead->utm_campaign = $data['utm_campaign'] ?? $lead->utm_campaign;
        $lead->utm_content = $data['utm_content'] ?? $lead->utm_content;
        $lead->utm_term = $data['utm_term'] ?? $lead->utm_term;

        // Meta extra
        $lead->meta = array_merge($lead->meta ?? [], $data['meta'] ?? []);

        $lead->save();

        // Registra o evento na timeline
        LeadEvent::create([
            'lead_id' => $lead->id,
            'event_type' => LeadEventType::Cadastro,
            'title' => $isNew ? 'Cadastro de Lead Realizado' : 'Lead Atualizou Cadastro',
            'description' => "Origem: Formulário SaaS (/register). Email: {$email}, Tel: {$normalizedPhone}",
            'metadata' => [
                'raw_phone' => $rawPhone,
                'normalized_phone' => $normalizedPhone,
                'saas_user_id' => $lead->saas_user_id,
                'utms' => [
                    'source' => $lead->utm_source,
                    'medium' => $lead->utm_medium,
                    'campaign' => $lead->utm_campaign,
                ],
            ],
            'score_delta' => 1,
            'occurred_at' => now(),
        ]);

        $lead->increment('score', 1);

        // Se o lead ainda não estiver assinado nem descadastrado, inicia o Fluxo 1
        if (!in_array($lead->status, [LeadStatus::Assinou, LeadStatus::Descadastrado])) {
            $this->flowEngine->enrollLead($lead, 'periodo-teste-15-dias');
        }

        return $lead;
    }
}
