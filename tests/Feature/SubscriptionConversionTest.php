<?php

namespace Tests\Feature;

use App\Enums\LeadEventType;
use App\Enums\LeadStatus;
use App\Jobs\MarkExpiredTrialsJob;
use App\Models\Lead;
use App\Models\LeadFlowRun;
use Database\Seeders\FlowSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionConversionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(FlowSeeder::class);
    }

    public function test_records_subscription_via_webhook_converts_lead_and_cancels_active_flows(): void
    {
        $lead = Lead::create([
            'name' => 'Roberto Vilar',
            'email' => 'roberto@vilar.com.br',
            'phone' => '+5511999993333',
            'status' => LeadStatus::EmFluxo1,
            'score' => 10,
        ]);

        $flowRun = LeadFlowRun::create([
            'lead_id' => $lead->id,
            'flow_id' => 1,
            'current_step_number' => 5,
            'started_at' => now()->subDays(4),
            'status' => 'active',
        ]);

        $payload = [
            'lead_id' => $lead->id,
            'email' => 'roberto@vilar.com.br',
            'plan_name' => 'Plano Anual Especial',
            'amount' => 1490.00,
            'transaction_id' => 'tx_eflips_998811',
            'subscribed_at' => now()->toIso8601String(),
        ];

        $response = $this->postJson('/api/webhooks/subscription', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status' => 'assinou',
            ]);

        $this->assertEquals(LeadStatus::Assinou, $lead->fresh()->status);
        $this->assertEquals(30, $lead->fresh()->score); // 10 + 20

        // Fluxo deve ter sido cancelado
        $this->assertEquals('cancelled', $flowRun->fresh()->status);

        // Assinatura persistida
        $this->assertDatabaseHas('subscriptions', [
            'lead_id' => $lead->id,
            'plan_name' => 'Plano Anual Especial',
            'amount' => 1490.00,
            'transaction_id' => 'tx_eflips_998811',
        ]);

        // Evento de conversão
        $this->assertDatabaseHas('lead_events', [
            'lead_id' => $lead->id,
            'event_type' => LeadEventType::Assinou->value,
        ]);
    }

    public function test_marks_lead_as_not_subscribed_after_30_days(): void
    {
        $expiredLead = Lead::create([
            'name' => 'Julio Antunes',
            'email' => 'julio@antunes.com.br',
            'phone' => '+5511999994444',
            'status' => LeadStatus::EmFluxo2,
            'cycle_ends_at' => now()->subHour(),
        ]);

        $activeLead = Lead::create([
            'name' => 'Clarice Falcão',
            'email' => 'clarice@empresa.com.br',
            'phone' => '+5511999995555',
            'status' => LeadStatus::EmFluxo1,
            'cycle_ends_at' => now()->addDays(20),
        ]);

        $job = new MarkExpiredTrialsJob();
        $job->handle();

        $this->assertEquals(LeadStatus::NaoAssinou, $expiredLead->fresh()->status);
        $this->assertEquals(LeadStatus::EmFluxo1, $activeLead->fresh()->status);
    }
}
