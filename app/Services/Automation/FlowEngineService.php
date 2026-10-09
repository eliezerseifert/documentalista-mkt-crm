<?php

namespace App\Services\Automation;

use App\Enums\LeadEventType;
use App\Enums\LeadStatus;
use App\Models\Flow;
use App\Models\Lead;
use App\Models\LeadEvent;
use App\Models\LeadFlowRun;

class FlowEngineService
{
    /**
     * Inscreve um lead no fluxo especificado por slug.
     */
    public function enrollLead(Lead $lead, string $flowSlug): ?LeadFlowRun
    {
        $flow = Flow::where('slug', $flowSlug)->where('is_active', true)->first();

        if (!$flow) {
            return null;
        }

        // Se o lead já assinou ou optou por descadastro, não inscreve
        if ($lead->status === LeadStatus::Assinou || $lead->status === LeadStatus::Descadastrado) {
            return null;
        }

        // Cancela ou encerra qualquer fluxo anterior que esteja ativo
        $lead->flowRuns()->where('status', 'active')->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        $run = LeadFlowRun::create([
            'lead_id' => $lead->id,
            'flow_id' => $flow->id,
            'current_step_number' => 1,
            'started_at' => now(),
            'status' => 'active',
        ]);

        // Atualiza status do Lead de acordo com o fluxo
        if ($flowSlug === 'periodo-teste-15-dias') {
            $lead->update(['status' => LeadStatus::EmFluxo1]);
        } elseif ($flowSlug === 'pos-teste-conversao-15-dias') {
            $lead->update(['status' => LeadStatus::EmFluxo2]);
        }

        LeadEvent::create([
            'lead_id' => $lead->id,
            'event_type' => LeadEventType::MensagemEnfileirada,
            'title' => 'Inscrito no ' . $flow->name,
            'description' => 'Lead ingressou na cadência automatizada.',
            'occurred_at' => now(),
        ]);

        return $run;
    }

    /**
     * Remove o lead de todos os fluxos ativos (ex: ao assinar).
     */
    public function cancelActiveFlows(Lead $lead, string $reason = 'Assinatura confirmada'): void
    {
        $activeRuns = $lead->flowRuns()->where('status', 'active')->get();

        foreach ($activeRuns as $run) {
            $run->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
            ]);

            LeadEvent::create([
                'lead_id' => $lead->id,
                'event_type' => LeadEventType::Assinou,
                'title' => 'Fluxo interrompido: ' . $reason,
                'description' => "O lead foi removido da cadência '{$run->flow->name}' porque converteu.",
                'occurred_at' => now(),
            ]);
        }
    }
}
