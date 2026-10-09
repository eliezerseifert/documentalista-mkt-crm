<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\IntegrationSetting;
use App\Models\Lead;
use App\Models\WebhookLog;
use App\Services\Lead\PhoneNormalizerService;
use App\Services\Lead\SubscriptionService;
use App\Services\Security\HmacSignatureValidator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Throwable;

class SubscriptionWebhookController extends Controller
{
    public function __construct(
        protected SubscriptionService $subscriptionService,
        protected PhoneNormalizerService $phoneNormalizer,
        protected HmacSignatureValidator $hmacValidator,
    ) {}

    public function handle(Request $request): JsonResponse
    {
        $payloadRaw = $request->getContent();
        $headers = $request->headers->all();
        $ip = $request->ip();

        // Configuração de secret do checkout
        $settings = IntegrationSetting::get('subscription_checkout', []);
        $secret = $settings['hmac_secret'] ?? null;

        if (!empty($secret)) {
            $signature = $request->header('X-Signature-SHA256') 
                ?? $request->header('X-Hub-Signature-256')
                ?? $request->header('X-Webhook-Signature');

            if (!$this->hmacValidator->isValid($signature, $payloadRaw, $secret)) {
                WebhookLog::create([
                    'origin' => 'subscription_checkout',
                    'ip_address' => $ip,
                    'headers' => $headers,
                    'payload' => $request->all(),
                    'response_status' => 401,
                    'error_message' => 'Assinatura HMAC de assinatura inválida.',
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Assinatura HMAC inválida.',
                ], 401);
            }
        }

        $validator = Validator::make($request->all(), [
            'lead_id' => ['nullable'],
            'email' => ['required_without_all:lead_id,phone', 'email'],
            'phone' => ['nullable', 'string'],
            'plan_name' => ['nullable', 'string'],
            'amount' => ['nullable', 'numeric'],
            'transaction_id' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            WebhookLog::create([
                'origin' => 'subscription_checkout',
                'ip_address' => $ip,
                'headers' => $headers,
                'payload' => $request->all(),
                'response_status' => 422,
                'error_message' => json_encode($validator->errors()),
            ]);

            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        // Localiza o lead
        $lead = null;

        if (!empty($data['lead_id'])) {
            $lead = Lead::find($data['lead_id']);
        }

        if (!$lead && !empty($data['email'])) {
            $lead = Lead::where('email', strtolower(trim($data['email'])))->first();
        }

        if (!$lead && !empty($data['phone'])) {
            $normalized = $this->phoneNormalizer->normalize($data['phone']);
            $lead = Lead::where('phone', $normalized)->first();
        }

        if (!$lead) {
            WebhookLog::create([
                'origin' => 'subscription_checkout',
                'ip_address' => $ip,
                'headers' => $headers,
                'payload' => $request->all(),
                'response_status' => 404,
                'error_message' => 'Lead não encontrado para registrar assinatura.',
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Lead não encontrado para o e-mail/telefone informado.',
            ], 404);
        }

        try {
            $subscription = $this->subscriptionService->recordConversion($lead, $data);

            WebhookLog::create([
                'origin' => 'subscription_checkout',
                'ip_address' => $ip,
                'headers' => $headers,
                'payload' => $request->all(),
                'response_status' => 200,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Assinatura registrada com sucesso. Lead convertido.',
                'lead_id' => $lead->id,
                'subscription_id' => $subscription->id,
                'status' => $lead->status->value,
            ], 200);

        } catch (Throwable $e) {
            WebhookLog::create([
                'origin' => 'subscription_checkout',
                'ip_address' => $ip,
                'headers' => $headers,
                'payload' => $request->all(),
                'response_status' => 500,
                'error_message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro interno ao processar assinatura: ' . $e->getMessage(),
            ], 500);
        }
    }
}
