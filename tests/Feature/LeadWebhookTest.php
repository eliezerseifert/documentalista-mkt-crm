<?php

namespace Tests\Feature;

use App\Enums\LeadEventType;
use App\Enums\LeadStatus;
use App\Models\IntegrationSetting;
use App\Models\Lead;
use App\Models\LeadEvent;
use Database\Seeders\FlowSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeadWebhookTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(FlowSeeder::class);
    }

    public function test_can_receive_valid_lead_via_webhook_and_enroll_in_flow_1(): void
    {
        $payload = [
            'name' => 'Dra. Camila Nogueira',
            'email' => 'camila.nogueira@direito.adv.br',
            'phone' => '(11) 98888-7777',
            'saas_user_id' => 'USR-998877',
            'utm_source' => 'google',
            'utm_campaign' => 'busca_certidoes',
        ];

        $response = $this->postJson('/api/webhooks/leads', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status' => 'em_fluxo_1',
            ]);

        $this->assertDatabaseHas('leads', [
            'email' => 'camila.nogueira@direito.adv.br',
            'phone' => '+5511988887777',
            'status' => LeadStatus::EmFluxo1->value,
            'saas_user_id' => 'USR-998877',
            'utm_source' => 'google',
        ]);

        $lead = Lead::where('email', 'camila.nogueira@direito.adv.br')->first();
        $this->assertNotNull($lead);
        $this->assertEquals(15, $lead->days_remaining_trial);

        // Verifica se registrou evento de cadastro
        $this->assertDatabaseHas('lead_events', [
            'lead_id' => $lead->id,
            'event_type' => LeadEventType::Cadastro->value,
        ]);

        // Verifica se inscreveu no Fluxo 1
        $this->assertDatabaseHas('lead_flow_runs', [
            'lead_id' => $lead->id,
            'status' => 'active',
        ]);
    }

    public function test_deduplicates_lead_by_email_and_updates_data(): void
    {
        $lead = Lead::create([
            'name' => 'Carlos Silva',
            'email' => 'carlos@silva.com.br',
            'phone' => '+5511977776666',
            'status' => LeadStatus::Novo,
        ]);

        $payload = [
            'name' => 'Carlos Silva Atualizado',
            'email' => 'carlos@silva.com.br',
            'phone' => '(11) 97777-6666',
            'saas_user_id' => 'USR-12345',
        ];

        $response = $this->postJson('/api/webhooks/leads', $payload);
        $response->assertStatus(200);

        $this->assertEquals(1, Lead::where('email', 'carlos@silva.com.br')->count());
        $this->assertEquals('Carlos Silva Atualizado', $lead->fresh()->name);
        $this->assertEquals('USR-12345', $lead->fresh()->saas_user_id);
    }

    public function test_enforces_hmac_signature_when_configured(): void
    {
        $secret = 'minha-chave-secreta-hmac-123456';
        IntegrationSetting::set('lead_capture', [
            'hmac_secret' => $secret,
        ]);

        $payload = [
            'name' => 'Paula Lima',
            'email' => 'paula@empresa.com.br',
            'phone' => '11999990000',
        ];

        $rawBody = json_encode($payload);

        // Tentativa sem header -> deve retornar 401
        $responseWithoutSig = $this->call('POST', '/api/webhooks/leads', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], $rawBody);

        $responseWithoutSig->assertStatus(401);

        // Com assinatura correta
        $validHash = hash_hmac('sha256', $rawBody, $secret);
        $responseWithSig = $this->call('POST', '/api/webhooks/leads', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_SIGNATURE_SHA256' => $validHash,
        ], $rawBody);

        $responseWithSig->assertStatus(200);
        $this->assertDatabaseHas('leads', ['email' => 'paula@empresa.com.br']);
    }
}
