<?php

namespace Tests\Feature;

use App\Enums\LeadEventType;
use App\Enums\LeadStatus;
use App\Models\Lead;
use App\Models\LeadEvent;
use App\Models\LeadFlowRun;
use Database\Seeders\FlowSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Tests\TestCase;

class EmailTrackingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(FlowSeeder::class);
    }

    public function test_tracks_email_open_via_pixel_and_increments_lead_score(): void
    {
        $lead = Lead::create([
            'name' => 'Luciana Medeiros',
            'email' => 'luciana@medeiros.adv.br',
            'phone' => '+5511999991111',
            'status' => LeadStatus::EmFluxo1,
            'score' => 5,
        ]);

        $token = Crypt::encrypt([
            'lead_id' => $lead->id,
            'step_id' => 1,
        ]);

        $response = $this->get('/t/open/' . $token);

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'image/gif');

        $this->assertEquals(6, $lead->fresh()->score);
        $this->assertDatabaseHas('lead_events', [
            'lead_id' => $lead->id,
            'event_type' => LeadEventType::EmailAberto->value,
            'channel' => 'email',
        ]);
    }

    public function test_unsubscribes_lead_cancels_active_flows_and_marks_status(): void
    {
        $lead = Lead::create([
            'name' => 'Gabriel Fonseca',
            'email' => 'gabriel@fonseca.com.br',
            'phone' => '+5511999992222',
            'status' => LeadStatus::EmFluxo1,
            'is_email_opted_out' => false,
        ]);

        $flowRun = LeadFlowRun::create([
            'lead_id' => $lead->id,
            'flow_id' => 1,
            'current_step_number' => 2,
            'started_at' => now(),
            'status' => 'active',
        ]);

        $token = Crypt::encrypt(['lead_id' => $lead->id]);

        $response = $this->get('/unsubscribe/' . $token);

        $response->assertStatus(200);
        $response->assertSee('Descadastro Concluído');

        $this->assertTrue($lead->fresh()->is_email_opted_out);
        $this->assertEquals(LeadStatus::Descadastrado, $lead->fresh()->status);
        $this->assertEquals('cancelled', $flowRun->fresh()->status);

        $this->assertDatabaseHas('lead_events', [
            'lead_id' => $lead->id,
            'event_type' => LeadEventType::OptOut->value,
            'channel' => 'email',
        ]);
    }
}
