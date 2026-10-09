<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\IntegrationSetting;
use App\Models\WebhookLog;
use App\Services\Lead\LeadCaptureService;
use App\Services\Security\HmacSignatureValidator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Throwable;

class LeadWebhookController extends Controller
{
    public function __construct(
        protected LeadCaptureService $leadCaptureService,
        protected HmacSignatureValidator $hmacValidator,
    ) {}

    public function handle(Request $request): JsonResponse
    {
        $payloadRaw = $request->getContent();
        $headers = $request->headers->all();
        $ip = $request->ip();

        // Obtém configuração do formulário
        $setting = IntegrationSetting::get('lead_capture', []);
        $secret = $setting['hmac_secret'] ?? null;

        // Se secret configurado, valida a assinatura
        if (!empty($secret)) {
            $signature = $request->header('X-Signature-SHA256') 
                ?? $request->header('X-Hub-Signature-256')
                ?? $request->header('X-Webhook-Signature');

            if (!$this->hmacValidator->isValid($signature, $payloadRaw, $secret)) {
                WebhookLog::create([
                    'origin' => 'form_capture',
                    'ip_address' => $ip,
                    'headers' => $headers,
                    'payload' => $request->all(),
                    'response_status' => 401,
                    'error_message' => 'Assinatura HMAC inválida ou ausente.',
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Assinatura HMAC inválida ou ausente.',
                ], 401);
            }
        }

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required_without:whatsapp', 'string', 'max:30'],
            'whatsapp' => ['nullable', 'string', 'max:30'],
            'saas_user_id' => ['nullable'],
            'registered_at' => ['nullable', 'date'],
            'utm_source' => ['nullable', 'string', 'max:255'],
            'utm_medium' => ['nullable', 'string', 'max:255'],
            'utm_campaign' => ['nullable', 'string', 'max:255'],
            'utm_content' => ['nullable', 'string', 'max:255'],
            'utm_term' => ['nullable', 'string', 'max:255'],
            'meta' => ['nullable', 'array'],
        ]);

        if ($validator->fails()) {
            WebhookLog::create([
                'origin' => 'form_capture',
                'ip_address' => $ip,
                'headers' => $headers,
                'payload' => $request->all(),
                'response_status' => 422,
                'error_message' => 'Dados de validação inválidos: ' . json_encode($validator->errors()),
            ]);

            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $lead = $this->leadCaptureService->process($validator->validated(), $ip, $headers);

            WebhookLog::create([
                'origin' => 'form_capture',
                'ip_address' => $ip,
                'headers' => $headers,
                'payload' => $request->all(),
                'response_status' => 200,
                'error_message' => null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Lead captado com sucesso e inscrito no Fluxo 1.',
                'lead_id' => $lead->id,
                'status' => $lead->status->value,
            ], 200);

        } catch (Throwable $e) {
            WebhookLog::create([
                'origin' => 'form_capture',
                'ip_address' => $ip,
                'headers' => $headers,
                'payload' => $request->all(),
                'response_status' => 500,
                'error_message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro interno ao processar lead: ' . $e->getMessage(),
            ], 500);
        }
    }
}
