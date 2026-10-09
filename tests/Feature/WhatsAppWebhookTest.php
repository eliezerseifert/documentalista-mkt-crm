<?php

namespace Tests\Feature;

use App\Enums\LeadEventType;
use App\Enums\LeadStatus;
use App\Enums\MessageDeliveryStatus;
use App\Models\IntegrationSetting;
use App\Models\Lead;
use App\Models\MessageLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WhatsAppWebhookTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        IntegrationSetting::set('whatsapp_cloud', [
            'verify_token' => 'meu_token_secreto_meta',
        ]);
    }

    public function test_verifies_webhook_challenge_with_correct_token(): void
    {
        $response = $this->get('/api/webhooks/whatsapp?hub_mode=subscribe&hub_verify_token=meu_token_secreto_meta&hub_challenge=11559955');

        $response->assertStatus(200);
        $this->assertEquals('11559955', $response->getContent());
    }

    public function test_rejects_webhook_challenge_with_invalid_token(): void
    {
        $response = $this->get('/api/webhooks/whatsapp?hub_mode=subscribe&hub_verify_token=token_errado&hub_challenge=11559955');

        $response->assertStatus(403);
    }

    public function test_updates_message_status_to_delivered_and_read(): void
    {
        $lead = Lead::create([
            'name' => 'Renato Duarte',
            'email' => 'renato@duarte.com',
            'phone' => '+5511998877665',
            'status' => LeadStatus::EmFluxo1,
        ]);

        $log = MessageLog::create([
            'lead_id' => $lead->id,
            'channel' => 'whatsapp',
            'direction' => 'outbound',
            'status' => MessageDeliveryStatus::Enviada,
            'external_message_id' => 'wamid.HBgLMTIzNDU2Nzg5',
            'content_sent' => 'Mensagem de teste',
        ]);

        // Simula evento da Meta de entrega (delivered)
        $deliveredPayload = [
            'entry' => [
                [
                    'changes' => [
                        [
                            'value' => [
                                'statuses' => [
                                    [
                                        'id' => 'wamid.HBgLMTIzNDU2Nzg5',
                                        'status' => 'delivered',
                                        'timestamp' => now()->timestamp,
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $res = $this->postJson('/api/webhooks/whatsapp', $deliveredPayload);
        $res->assertStatus(200);

        $this->assertEquals(MessageDeliveryStatus::Entregue, $log->fresh()->status);
        $this->assertNotNull($log->fresh()->delivered_at);

        // Simula evento da Meta de leitura (read)
        $readPayload = [
            'entry' => [
                [
                    'changes' => [
                        [
                            'value' => [
                                'statuses' => [
                                    [
                                        'id' => 'wamid.HBgLMTIzNDU2Nzg5',
                                        'status' => 'read',
                                        'timestamp' => now()->timestamp,
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $resRead = $this->postJson('/api/webhooks/whatsapp', $readPayload);
        $resRead->assertStatus(200);

        $this->assertEquals(MessageDeliveryStatus::Lida, $log->fresh()->status);
        $this->assertNotNull($log->fresh()->read_at);
        $this->assertDatabaseHas('lead_events', [
            'lead_id' => $lead->id,
            'event_type' => LeadEventType::MensagemLida->value,
        ]);
    }

    public function test_handles_opt_out_when_lead_replies_sair(): void
    {
        $lead = Lead::create([
            'name' => 'Juliana Paes',
            'email' => 'juliana@empresa.com',
            'phone' => '+5511988889999',
            'status' => LeadStatus::EmFluxo1,
            'is_whatsapp_opted_out' => false,
        ]);

        $incomingPayload = [
            'entry' => [
                [
                    'changes' => [
                        [
                            'value' => [
                                'contacts' => [
                                    ['profile' => ['name' => 'Juliana Paes']]
                                ],
                                'messages' => [
                                    [
                                        'from' => '5511988889999',
                                        'id' => 'wamid.INBOUND123',
                                        'text' => [
                                            'body' => 'SAIR por favor',
                                        ],
                                        'type' => 'text',
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $res = $this->postJson('/api/webhooks/whatsapp', $incomingPayload);
        $res->assertStatus(200);

        $this->assertTrue($lead->fresh()->is_whatsapp_opted_out);
        $this->assertDatabaseHas('lead_events', [
            'lead_id' => $lead->id,
            'event_type' => LeadEventType::OptOut->value,
        ]);
    }
}
