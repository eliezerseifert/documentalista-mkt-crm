<?php

namespace App\Http\Controllers\Tracking;

use App\Enums\LeadEventType;
use App\Enums\LeadStatus;
use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\LeadEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Throwable;

class UnsubscribeController extends Controller
{
    /**
     * Processa o descadastro em 1 clique (LGPD).
     */
    public function unsubscribe(string $token, Request $request)
    {
        $lead = null;

        try {
            $payload = Crypt::decrypt($token);
            $leadId = $payload['lead_id'] ?? null;

            if ($leadId) {
                $lead = Lead::find($leadId);
            }
        } catch (Throwable $e) {
            // Token inválido
        }

        if (!$lead) {
            return view('unsubscribe.invalid');
        }

        if (!$lead->is_email_opted_out) {
            $lead->update([
                'is_email_opted_out' => true,
                'status' => LeadStatus::Descadastrado,
            ]);

            // Cancela fluxos ativos
            $lead->flowRuns()->where('status', 'active')->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
            ]);

            LeadEvent::create([
                'lead_id' => $lead->id,
                'event_type' => LeadEventType::OptOut,
                'channel' => 'email',
                'title' => 'Opt-out solicitado: descadastro de e-mail',
                'description' => 'Lead clicou no link de descadastro (LGPD). Fluxos cancelados.',
                'metadata' => [
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ],
                'occurred_at' => now(),
            ]);
        }

        return view('unsubscribe.success', ['lead' => $lead]);
    }
}
