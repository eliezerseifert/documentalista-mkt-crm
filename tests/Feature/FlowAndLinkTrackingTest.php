<?php

namespace Tests\Feature;

use App\Enums\FlowChannel;
use App\Enums\LeadEventType;
use App\Enums\LeadStatus;
use App\Jobs\SendFlowStepMessageJob;
use App\Models\Flow;
use App\Models\FlowStep;
use App\Models\Lead;
use App\Models\MessageLog;
use App\Models\StepMessage;
use App\Models\TrackedLink;
use App\Services\Automation\MessageVariableParserService;
use Database\Seeders\FlowSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class FlowAndLinkTrackingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(FlowSeeder::class);
    }

    public function test_link_click_tracks_event_updates_score_and_redirects_to_checkout(): void
    {
        $lead = Lead::create([
            'name' => 'Dr. Fernando Rocha',
            'email' => 'fernando@rocha.adv.br',
            'phone' => '+5511977771234',
            'status' => LeadStatus::EmFluxo1,
            'score' => 2,
        ]);

        $destination = 'https://checkout.v2.e-flips.com.br?plan=pro';

        $trackedLink = TrackedLink::create([
            'token' => 'token_checkout_teste_123',
            'lead_id' => $lead->id,
            'destination_url' => $destination,
        ]);

        $response = $this->get('/t/click/' . $trackedLink->token);

        $response->assertStatus(302);
        $response->assertRedirect($destination);

        $this->assertEquals(1, $trackedLink->fresh()->clicks_count);
        $this->assertNotNull($trackedLink->fresh()->first_clicked_at);

        // Score deve ter somado +3 (clique) + 5 (checkout) = 2 + 8 = 10
        $this->assertEquals(10, $lead->fresh()->score);
        $this->assertEquals(LeadStatus::Engajado, $lead->fresh()->status);

        $this->assertDatabaseHas('lead_events', [
            'lead_id' => $lead->id,
            'event_type' => LeadEventType::LinkClicado->value,
        ]);

        $this->assertDatabaseHas('lead_events', [
            'lead_id' => $lead->id,
            'event_type' => LeadEventType::ChegouCheckout->value,
        ]);
    }

    public function test_send_flow_step_message_job_guarantees_strict_idempotency(): void
    {
        $lead = Lead::create([
            'name' => 'Beatriz Santos',
            'email' => 'beatriz@cartorio.com.br',
            'phone' => '+5511988880000',
            'status' => LeadStatus::EmFluxo1,
            'trial_ends_at' => now()->addDays(14),
        ]);

        $step = FlowStep::first();

        // 1ª Execução do Job
        $job = new SendFlowStepMessageJob($lead->id, $step->id);
        $job->handle(
            app(MessageVariableParserService::class),
            app(\App\Services\WhatsApp\WhatsAppCloudApiService::class),
            app(\App\Services\Email\DynamicSmtpTransportFactory::class),
        );

        $initialLogsCount = MessageLog::where('lead_id', $lead->id)->where('flow_step_id', $step->id)->count();
        $this->assertGreaterThan(0, $initialLogsCount);

        // 2ª Execução do Job (mesmo lead, mesmo step) -> NÃO deve duplicar
        $job->handle(
            app(MessageVariableParserService::class),
            app(\App\Services\WhatsApp\WhatsAppCloudApiService::class),
            app(\App\Services\Email\DynamicSmtpTransportFactory::class),
        );

        $afterLogsCount = MessageLog::where('lead_id', $lead->id)->where('flow_step_id', $step->id)->count();
        $this->assertEquals($initialLogsCount, $afterLogsCount);
    }
}
