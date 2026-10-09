<?php

namespace App\Http\Controllers\Tracking;

use App\Enums\LeadEventType;
use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\LeadEvent;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Crypt;
use Throwable;

class EmailOpenTrackerController extends Controller
{
    /**
     * Rastreia a abertura de e-mail através do pixel transparente 1x1.
     */
    public function track(string $token, Request $request): Response
    {
        try {
            $payload = Crypt::decrypt($token);
            $leadId = $payload['lead_id'] ?? null;
            $stepId = $payload['step_id'] ?? null;

            if ($leadId) {
                $lead = Lead::find($leadId);

                if ($lead) {
                    // Evita duplicar múltiplos eventos de abertura dentro de 5 minutos
                    $recentOpen = LeadEvent::where('lead_id', $lead->id)
                        ->where('event_type', LeadEventType::EmailAberto)
                        ->where('occurred_at', '>=', now()->subMinutes(5))
                        ->exists();

                    if (!$recentOpen) {
                        LeadEvent::create([
                            'lead_id' => $lead->id,
                            'event_type' => LeadEventType::EmailAberto,
                            'channel' => 'email',
                            'title' => 'E-mail aberto pelo lead',
                            'description' => "Leitura identificada via pixel. IP: " . $request->ip(),
                            'metadata' => [
                                'step_id' => $stepId,
                                'user_agent' => $request->userAgent(),
                                'ip' => $request->ip(),
                            ],
                            'score_delta' => 1,
                            'occurred_at' => now(),
                        ]);

                        $lead->increment('score', 1);
                    }
                }
            }
        } catch (Throwable $e) {
            // Ignora silenciosamente para não quebrar a imagem no cliente de e-mail
        }

        // GIF transparente de 1x1 pixel (43 bytes padrão)
        $transparentGif = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');

        return response($transparentGif, 200, [
            'Content-Type' => 'image/gif',
            'Cache-Control' => 'no-cache, no-store, must-revalidate, private',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }
}
