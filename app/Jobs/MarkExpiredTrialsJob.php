<?php

namespace App\Jobs;

use App\Enums\LeadEventType;
use App\Enums\LeadStatus;
use App\Models\Lead;
use App\Models\LeadEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class MarkExpiredTrialsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $expiredLeads = Lead::whereNotIn('status', [LeadStatus::Assinou, LeadStatus::Descadastrado, LeadStatus::NaoAssinou])
            ->where(function ($q) {
                $q->where('cycle_ends_at', '<=', now())
                  ->orWhere('created_at', '<=', now()->subDays(30));
            })
            ->get();

        foreach ($expiredLeads as $lead) {
            $lead->update([
                'status' => LeadStatus::NaoAssinou,
            ]);

            // Cancela fluxos ativos
            $lead->flowRuns()->where('status', 'active')->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            LeadEvent::create([
                'lead_id' => $lead->id,
                'event_type' => LeadEventType::OptOut,
                'title' => 'Ciclo de 30 dias expirado: Marcado como Não Assinou',
                'description' => 'O lead completou os 30 dias do ciclo (15 dias de teste + 15 dias de pós-teste) sem assinar.',
                'occurred_at' => now(),
            ]);
        }
    }
}
