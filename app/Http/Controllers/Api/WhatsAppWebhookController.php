<?php

namespace App\Http\Controllers\Api;

use App\Enums\LeadEventType;
use App\Enums\MessageDeliveryStatus;
use App\Http\Controllers\Controller;
use App\Models\IntegrationSetting;
use App\Models\Lead;
use App\Models\LeadEvent;
use App\Models\MessageLog;
use App\Models\WebhookLog;
use App\Services\Lead\PhoneNormalizerService;
use App\Services\WhatsApp\WhatsAppOptOutService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Throwable;

class WhatsAppWebhookController extends Controller
{
    public function __construct(
        protected PhoneNormalizerService $phoneNormalizer,
        protected WhatsAppOptOutService $optOutService,
    ) {}

    /**
     * Verificação de Webhook da Meta (GET).
     */
    public function verify(Request $request): Response
    {
        $mode = $request->query('hub_mode') ?? $request->query('hub.mode');
        $token = $request->query('hub_verify_token') ?? $request->query('hub.verify_token');
        $challenge = $request->query('hub_challenge') ?? $request->query('hub.challenge');

        $settings = IntegrationSetting::get('whatsapp_cloud', []);
        $configuredToken = $settings['verify_token'] ?? 'documentalista_webhook_token';

        if ($mode === 'subscribe' && $token === $configuredToken) {
            return response($challenge, 200)->header('Content-Type', 'text/plain');
        }

        return response('Acesso não autorizado', 403);
    }

    /**
     * Recebimento de eventos da Meta (POST).
     */
    public function handle(Request $request): Response
    {
        $payload = $request->all();
        $ip = $request->ip();

        WebhookLog::create([
            'origin' => 'whatsapp',
            'ip_address' => $ip,
            'headers' => $request->headers->all(),
            'payload' => $payload,
            'response_status' => 200,
        ]);

        try {
            $entries = $payload['entry'] ?? [];

            foreach ($entries as $entry) {
                $changes = $entry['changes'] ?? [];

                foreach ($changes as $change) {
                    $value = $change['value'] ?? [];

                    // 1. Tratamento de Statuses (sent, delivered, read, failed)
                    if (!empty($value['statuses'])) {
                        $this->handleStatuses($value['statuses']);
                    }

                    // 2. Tratamento de Mensagens Recebidas (inbound)
                    if (!empty($value['messages'])) {
                        $this->handleIncomingMessages($value['messages'], $value['contacts'] ?? []);
                    }
                }
            }
        } catch (Throwable $e) {
            WebhookLog::create([
                'origin' => 'whatsapp_error',
                'ip_address' => $ip,
                'payload' => ['error' => $e->getMessage()],
                'response_status' => 500,
                'error_message' => $e->getMessage(),
            ]);
        }

        return response('EVENT_RECEIVED', 200);
    }

    protected function handleStatuses(array $statuses): void
    {
        foreach ($statuses as $statusData) {
            $messageId = $statusData['id'] ?? null;
            $statusName = $statusData['status'] ?? null;
            $timestamp = isset($statusData['timestamp']) 
                ? Carbon::createFromTimestamp($statusData['timestamp']) 
                : now();

            if (!$messageId) {
                continue;
            }

            $log = MessageLog::where('external_message_id', $messageId)->first();
            if (!$log) {
                continue;
            }

            $lead = $log->lead;

            switch ($statusName) {
                case 'sent':
                    $log->update([
                        'status' => MessageDeliveryStatus::Enviada,
                        'sent_at' => $timestamp,
                    ]);
                    break;

                case 'delivered':
                    $log->update([
                        'status' => MessageDeliveryStatus::Entregue,
                        'delivered_at' => $timestamp,
                    ]);

                    if ($lead) {
                        LeadEvent::create([
                            'lead_id' => $lead->id,
                            'event_type' => LeadEventType::MensagemEntregue,
                            'channel' => 'whatsapp',
                            'title' => 'Mensagem WhatsApp entregue no aparelho',
                            'occurred_at' => $timestamp,
                        ]);
                    }
                    break;

                case 'read':
                    $log->update([
                        'status' => MessageDeliveryStatus::Lida,
                        'read_at' => $timestamp,
                    ]);

                    if ($lead) {
                        LeadEvent::create([
                            'lead_id' => $lead->id,
                            'event_type' => LeadEventType::MensagemLida,
                            'channel' => 'whatsapp',
                            'title' => 'Mensagem WhatsApp lida pelo lead',
                            'score_delta' => 1,
                            'occurred_at' => $timestamp,
                        ]);
                        $lead->increment('score', 1);
                    }
                    break;

                case 'failed':
                    $errors = $statusData['errors'] ?? [];
                    $errorText = json_encode($errors);

                    $log->update([
                        'status' => MessageDeliveryStatus::Falhou,
                        'error_message' => $errorText,
                    ]);
                    break;
            }
        }
    }

    protected function handleIncomingMessages(array $messages, array $contacts): void
    {
        foreach ($messages as $msg) {
            $fromRaw = $msg['from'] ?? null;
            if (!$fromRaw) {
                continue;
            }

            $normalizedPhone = $this->phoneNormalizer->normalize($fromRaw);
            $lead = Lead::where('phone', $normalizedPhone)->first();

            $textBody = '';
            if (isset($msg['text']['body'])) {
                $textBody = $msg['text']['body'];
            } elseif (isset($msg['button']['text'])) {
                $textBody = $msg['button']['text'];
            } elseif (isset($msg['interactive']['button_reply']['title'])) {
                $textBody = $msg['interactive']['button_reply']['title'];
            }

            if (!$lead) {
                // Cria lead se não existir
                $contactName = $contacts[0]['profile']['name'] ?? 'Contato WhatsApp';
                $lead = Lead::create([
                    'name' => $contactName,
                    'email' => 'wpp_' . ltrim($normalizedPhone, '+') . '@inbound.documentalista.com.br',
                    'phone' => $normalizedPhone,
                    'status' => \App\Enums\LeadStatus::Novo,
                    'last_whatsapp_received_at' => now(),
                ]);
            } else {
                // Renova janela de 24h
                $lead->update([
                    'last_whatsapp_received_at' => now(),
                ]);
            }

            // Registra mensagem no log
            MessageLog::create([
                'lead_id' => $lead->id,
                'channel' => 'whatsapp',
                'direction' => 'inbound',
                'status' => MessageDeliveryStatus::Entregue,
                'external_message_id' => $msg['id'] ?? null,
                'content_sent' => $textBody,
                'delivered_at' => now(),
            ]);

            // Verifica solicitação de Opt-out
            if ($this->optOutService->isOptOutRequest($textBody)) {
                $this->optOutService->processOptOut($lead, $textBody);
                continue;
            }

            // Resposta normal do Lead
            LeadEvent::create([
                'lead_id' => $lead->id,
                'event_type' => LeadEventType::RespostaWhatsApp,
                'channel' => 'whatsapp',
                'title' => 'Lead respondeu no WhatsApp',
                'description' => $textBody,
                'score_delta' => 2,
                'occurred_at' => now(),
            ]);

            $lead->increment('score', 2);
        }
    }
}
