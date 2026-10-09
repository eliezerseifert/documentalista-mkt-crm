<?php

namespace App\Jobs;

use App\Enums\FlowChannel;
use App\Enums\LeadEventType;
use App\Enums\LeadStatus;
use App\Enums\MessageDeliveryStatus;
use App\Mail\DynamicCampaignMail;
use App\Models\FlowStep;
use App\Models\Lead;
use App\Models\LeadEvent;
use App\Models\MessageLog;
use App\Services\Automation\MessageVariableParserService;
use App\Services\Email\DynamicSmtpTransportFactory;
use App\Services\WhatsApp\WhatsAppCloudApiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendFlowStepMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [10, 30, 60];

    public function __construct(
        public int $leadId,
        public int $flowStepId,
    ) {}

    public function handle(
        MessageVariableParserService $parser,
        WhatsAppCloudApiService $whatsAppService,
        DynamicSmtpTransportFactory $smtpFactory,
    ): void {
        $lead = Lead::find($this->leadId);
        $step = FlowStep::with(['flow', 'whatsappMessage', 'emailMessage'])->find($this->flowStepId);

        if (!$lead || !$step) {
            return;
        }

        // Não envia se o lead já assinou ou se descadastrou totalmente
        if ($lead->status === LeadStatus::Assinou || $lead->status === LeadStatus::Descadastrado) {
            return;
        }

        // Verifica canal do passo
        $shouldSendWhatsApp = in_array($step->channel, [FlowChannel::WhatsApp, FlowChannel::Ambos]) && !$lead->is_whatsapp_opted_out;
        $shouldSendEmail = in_array($step->channel, [FlowChannel::Email, FlowChannel::Ambos]) && !$lead->is_email_opted_out;

        // -------------------------------------------------------------
        // 1. DISPARO WHATSAPP (com idempotência estrita)
        // -------------------------------------------------------------
        if ($shouldSendWhatsApp && $step->whatsappMessage) {
            $alreadySentWpp = MessageLog::where('lead_id', $lead->id)
                ->where('flow_step_id', $step->id)
                ->where('channel', 'whatsapp')
                ->exists();

            if (!$alreadySentWpp) {
                $wppMsg = $step->whatsappMessage;
                $parsedBody = $parser->parse($wppMsg->body_text, $lead, $step);
                $buttonUrl = $wppMsg->button_url ? $parser->parse($wppMsg->button_url, $lead, $step) : null;

                $log = MessageLog::create([
                    'lead_id' => $lead->id,
                    'flow_step_id' => $step->id,
                    'channel' => 'whatsapp',
                    'direction' => 'outbound',
                    'status' => MessageDeliveryStatus::Enfileirada,
                    'content_sent' => $parsedBody,
                ]);

                try {
                    // Avalia se está dentro da janela gratuita de 24h
                    if ($lead->isWithinWhatsApp24hWindow()) {
                        if ($wppMsg->button_label && $buttonUrl) {
                            $res = $whatsAppService->sendInteractiveButtonMessage(
                                $lead->phone,
                                $parsedBody,
                                $wppMsg->button_label,
                                $buttonUrl
                            );
                        } else {
                            $res = $whatsAppService->sendTextMessage($lead->phone, $parsedBody);
                        }
                    } else {
                        // Fora da janela: usa template da Meta
                        $templateName = $wppMsg->whatsapp_template_name ?: 'lead_trial_step_' . $step->step_number;
                        $res = $whatsAppService->sendTemplateMessage($lead->phone, $templateName, 'pt_BR');
                    }

                    if ($res['success']) {
                        $log->update([
                            'status' => MessageDeliveryStatus::Enviada,
                            'external_message_id' => $res['message_id'] ?? null,
                            'sent_at' => now(),
                        ]);

                        LeadEvent::create([
                            'lead_id' => $lead->id,
                            'event_type' => LeadEventType::MensagemEnviada,
                            'channel' => 'whatsapp',
                            'title' => "WhatsApp enviado: Etapa #{$step->step_number}",
                            'description' => $parsedBody,
                            'occurred_at' => now(),
                        ]);
                    } else {
                        $log->update([
                            'status' => MessageDeliveryStatus::Falhou,
                            'error_message' => $res['error'] ?? 'Falha no disparo WhatsApp',
                        ]);
                    }
                } catch (Throwable $e) {
                    $log->update([
                        'status' => MessageDeliveryStatus::Falhou,
                        'error_message' => $e->getMessage(),
                    ]);
                }
            }
        }

        // -------------------------------------------------------------
        // 2. DISPARO E-MAIL (com idempotência estrita)
        // -------------------------------------------------------------
        if ($shouldSendEmail && $step->emailMessage) {
            $alreadySentEmail = MessageLog::where('lead_id', $lead->id)
                ->where('flow_step_id', $step->id)
                ->where('channel', 'email')
                ->exists();

            if (!$alreadySentEmail) {
                $emailMsg = $step->emailMessage;
                $parsedSubject = $parser->parse($emailMsg->subject ?? 'Novidades Documentalista', $lead, $step);
                $parsedBody = $parser->parse($emailMsg->body_text, $lead, $step);
                $buttonUrl = $emailMsg->button_url ? $parser->parse($emailMsg->button_url, $lead, $step) : null;

                $log = MessageLog::create([
                    'lead_id' => $lead->id,
                    'flow_step_id' => $step->id,
                    'channel' => 'email',
                    'direction' => 'outbound',
                    'status' => MessageDeliveryStatus::Enfileirada,
                    'content_sent' => "Assunto: {$parsedSubject}\n\n{$parsedBody}",
                ]);

                try {
                    $openToken = Crypt::encrypt([
                        'lead_id' => $lead->id,
                        'step_id' => $step->id,
                    ]);

                    $unsubscribeToken = Crypt::encrypt([
                        'lead_id' => $lead->id,
                    ]);

                    $mailable = new DynamicCampaignMail(
                        lead: $lead,
                        mailSubject: $parsedSubject,
                        bodyHtml: $parsedBody,
                        buttonLabel: $emailMsg->button_label,
                        buttonUrl: $buttonUrl,
                        imagePath: $emailMsg->image_path,
                        openTrackingToken: $openToken,
                        unsubscribeToken: $unsubscribeToken,
                    );

                    $hasCustomSmtp = $smtpFactory->configureMailer();
                    $mailer = $hasCustomSmtp ? Mail::mailer('dynamic_smtp') : Mail::mailer();

                    $mailer->to($lead->email)->send($mailable);

                    $log->update([
                        'status' => MessageDeliveryStatus::Enviada,
                        'sent_at' => now(),
                    ]);

                    LeadEvent::create([
                        'lead_id' => $lead->id,
                        'event_type' => LeadEventType::MensagemEnviada,
                        'channel' => 'email',
                        'title' => "E-mail enviado: Etapa #{$step->step_number}",
                        'description' => "Assunto: {$parsedSubject}",
                        'occurred_at' => now(),
                    ]);

                } catch (Throwable $e) {
                    $log->update([
                        'status' => MessageDeliveryStatus::Falhou,
                        'error_message' => $e->getMessage(),
                    ]);
                    Log::error('Erro no envio de e-mail de automação', ['error' => $e->getMessage()]);
                }
            }
        }
    }
}
