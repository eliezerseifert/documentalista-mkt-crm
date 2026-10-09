<?php

namespace App\Http\Controllers\Tracking;

use App\Enums\LeadEventType;
use App\Enums\LeadStatus;
use App\Http\Controllers\Controller;
use App\Models\LeadEvent;
use App\Models\TrackedLink;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LinkClickTrackerController extends Controller
{
    /**
     * Rastreia o clique no link, atualiza score e redireciona ao destino final.
     */
    public function redirect(string $token, Request $request): RedirectResponse
    {
        $tracked = TrackedLink::with('lead', 'flowStep')->where('token', $token)->first();

        if (!$tracked) {
            return redirect('https://checkout.v2.e-flips.com.br');
        }

        $lead = $tracked->lead;

        // Atualiza contadores do link
        $tracked->increment('clicks_count');
        $tracked->update([
            'first_clicked_at' => $tracked->first_clicked_at ?? now(),
            'last_clicked_at' => now(),
        ]);

        if ($lead) {
            // Registra evento de clique
            LeadEvent::create([
                'lead_id' => $lead->id,
                'event_type' => LeadEventType::LinkClicado,
                'title' => 'Link clicado pelo lead',
                'description' => "Destino: {$tracked->destination_url}",
                'metadata' => [
                    'step_id' => $tracked->flow_step_id,
                    'destination_url' => $tracked->destination_url,
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ],
                'score_delta' => 3,
                'occurred_at' => now(),
            ]);

            $lead->increment('score', 3);

            // Se for link de checkout
            if (str_contains($tracked->destination_url, 'checkout.v2.e-flips.com.br')) {
                LeadEvent::create([
                    'lead_id' => $lead->id,
                    'event_type' => LeadEventType::ChegouCheckout,
                    'title' => 'Lead chegou ao Checkout de Assinatura',
                    'description' => 'Acessou o checkout de pagamento em checkout.v2.e-flips.com.br',
                    'score_delta' => 5,
                    'occurred_at' => now(),
                ]);

                $lead->increment('score', 5);

                if (in_array($lead->status, [LeadStatus::Novo, LeadStatus::EmFluxo1, LeadStatus::EmFluxo2])) {
                    $lead->update(['status' => LeadStatus::Engajado]);
                }
            }
        }

        return redirect()->away($tracked->destination_url);
    }
}
