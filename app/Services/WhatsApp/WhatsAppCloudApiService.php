<?php

namespace App\Services\WhatsApp;

use App\Models\IntegrationSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class WhatsAppCloudApiService
{
    protected ?string $phoneNumberId;
    protected ?string $wabaId;
    protected ?string $accessToken;
    protected string $apiVersion;

    public function __construct()
    {
        $settings = IntegrationSetting::get('whatsapp_cloud', []);
        $this->phoneNumberId = $settings['phone_number_id'] ?? null;
        $this->wabaId = $settings['waba_id'] ?? null;
        $this->accessToken = $settings['access_token'] ?? null;
        $this->apiVersion = $settings['api_version'] ?? 'v20.0';
    }

    protected function getBaseUrl(): string
    {
        return "https://graph.facebook.com/{$this->apiVersion}";
    }

    public function isConfigured(): bool
    {
        return !empty($this->phoneNumberId) && !empty($this->accessToken);
    }

    /**
     * Testa a conexão buscando os detalhes do número de telefone na Meta.
     */
    public function testConnection(): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'Credenciais do WhatsApp Cloud API não configuradas.',
            ];
        }

        try {
            $response = Http::withToken($this->accessToken)
                ->timeout(10)
                ->get("{$this->getBaseUrl()}/{$this->phoneNumberId}");

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'message' => 'Conexão estabelecida com sucesso!',
                    'details' => [
                        'display_phone_number' => $data['display_phone_number'] ?? null,
                        'verified_name' => $data['verified_name'] ?? null,
                        'quality_rating' => $data['quality_rating'] ?? null,
                    ],
                ];
            }

            return [
                'success' => false,
                'message' => 'Erro na Meta: ' . ($response->json('error.message') ?? $response->body()),
            ];
        } catch (Throwable $e) {
            return [
                'success' => false,
                'message' => 'Exceção na conexão: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Envia mensagem de texto livre (dentro da janela de 24h).
     */
    public function sendTextMessage(string $to, string $text): array
    {
        // Remove o '+' do telefone para o formato exigido pela Meta
        $recipient = ltrim($to, '+');

        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $recipient,
            'type' => 'text',
            'text' => [
                'preview_url' => true,
                'body' => $text,
            ],
        ];

        return $this->dispatchMessage($payload);
    }

    /**
     * Envia mensagem interativa com botão CTA / link.
     */
    public function sendInteractiveButtonMessage(string $to, string $text, string $buttonLabel, string $buttonUrl): array
    {
        $recipient = ltrim($to, '+');

        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $recipient,
            'type' => 'interactive',
            'interactive' => [
                'type' => 'cta_url',
                'body' => [
                    'text' => $text,
                ],
                'action' => [
                    'name' => 'cta_url',
                    'parameters' => [
                        'display_text' => $buttonLabel,
                        'url' => $buttonUrl,
                    ],
                ],
            ],
        ];

        return $this->dispatchMessage($payload);
    }

    /**
     * Envia mensagem com imagem e legenda.
     */
    public function sendImageMessage(string $to, string $imageUrl, ?string $caption = null): array
    {
        $recipient = ltrim($to, '+');

        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $recipient,
            'type' => 'image',
            'image' => array_filter([
                'link' => $imageUrl,
                'caption' => $caption,
            ]),
        ];

        return $this->dispatchMessage($payload);
    }

    /**
     * Envia Message Template aprovado pela Meta (fora da janela de 24h).
     */
    public function sendTemplateMessage(string $to, string $templateName, string $language = 'pt_BR', array $components = []): array
    {
        $recipient = ltrim($to, '+');

        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $recipient,
            'type' => 'template',
            'template' => [
                'name' => $templateName,
                'language' => [
                    'code' => $language,
                ],
                'components' => $components,
            ],
        ];

        return $this->dispatchMessage($payload);
    }

    protected function dispatchMessage(array $payload): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'error' => 'WhatsApp Cloud API não está configurado.',
            ];
        }

        try {
            $response = Http::withToken($this->accessToken)
                ->timeout(15)
                ->post("{$this->getBaseUrl()}/{$this->phoneNumberId}/messages", $payload);

            if ($response->successful()) {
                $data = $response->json();
                $messageId = $data['messages'][0]['id'] ?? null;

                return [
                    'success' => true,
                    'message_id' => $messageId,
                    'response' => $data,
                ];
            }

            $errorMsg = $response->json('error.message') ?? $response->body();
            Log::warning('Falha no envio WhatsApp Cloud API', ['response' => $response->json()]);

            return [
                'success' => false,
                'error' => $errorMsg,
                'response' => $response->json(),
            ];
        } catch (Throwable $e) {
            Log::error('Exceção ao disparar WhatsApp Cloud API', ['error' => $e->getMessage()]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
