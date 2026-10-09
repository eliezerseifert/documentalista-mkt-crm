<?php

namespace App\Jobs;

use App\Enums\LeadStatus;
use App\Models\FlowStep;
use App\Models\LeadFlowRun;
use App\Services\Automation\FlowEngineService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessDueFlowStepsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(FlowEngineService $flowEngine): void
    {
        $now = Carbon::now('America/Sao_Paulo');
        $currentTimeString = $now->format('H:i:s');

        // Busca todas as execuções de fluxos ativas
        $activeRuns = LeadFlowRun::with(['lead', 'flow.steps'])
            ->where('status', 'active')
            ->whereHas('flow', fn ($q) => $q->where('is_active', true))
            ->get();

        foreach ($activeRuns as $run) {
            $lead = $run->lead;

            if (!$lead || in_array($lead->status, [LeadStatus::Assinou, LeadStatus::Descadastrado])) {
                $run->update([
                    'status' => 'cancelled',
                    'cancelled_at' => now(),
                ]);
                continue;
            }

            // Dias decorridos desde o início do fluxo (fuso America/Sao_Paulo)
            $startedAt = $run->started_at->setTimezone('America/Sao_Paulo');
            $daysPassed = (int) $startedAt->diffInDays($now);

            // Busca a etapa correspondente
            $step = FlowStep::where('flow_id', $run->flow_id)
                ->where('is_active', true)
                ->where('day_offset', '<=', $daysPassed)
                ->where('step_number', '>=', $run->current_step_number)
                ->orderBy('step_number')
                ->first();

            if ($step) {
                // Se já é o dia e o horário de envio foi atingido
                $isDueToday = ($step->day_offset === $daysPassed);
                $timeReached = ($currentTimeString >= $step->send_time);

                // Se já passou do dia planejado ou se hoje já atingiu o horário
                if ($step->day_offset < $daysPassed || ($isDueToday && $timeReached)) {
                    SendFlowStepMessageJob::dispatch($lead->id, $step->id);

                    // Avança o ponteiro de etapa
                    $run->update([
                        'current_step_number' => $step->step_number + 1,
                    ]);
                }
            } else {
                // Verifica se o fluxo foi completamente finalizado
                $totalSteps = $run->flow->steps()->where('is_active', true)->count();
                if ($run->current_step_number > $totalSteps || $daysPassed >= $run->flow->total_days) {
                    $run->update([
                        'status' => 'completed',
                        'completed_at' => now(),
                    ]);

                    // Se terminou o Fluxo 1 (Período de teste) e o lead ainda não assinou, inscreve no Fluxo 2!
                    if ($run->flow->slug === 'periodo-teste-15-dias' && $lead->status !== LeadStatus::Assinou) {
                        $flowEngine->enrollLead($lead, 'pos-teste-conversao-15-dias');
                    }
                }
            }
        }
    }
}
