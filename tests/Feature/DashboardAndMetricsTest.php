<?php

namespace Tests\Feature;

use App\Filament\Widgets\ChannelEngagementRateWidget;
use App\Filament\Widgets\LeadFunnelChartWidget;
use App\Filament\Widgets\LeadStatsOverviewWidget;
use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Database\Seeders\FlowSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardAndMetricsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AdminUserSeeder::class);
        $this->seed(FlowSeeder::class);
    }

    public function test_can_access_admin_login(): void
    {
        $response = $this->get('/admin/login');
        $response->assertStatus(200);
    }

    public function test_authenticated_admin_can_access_dashboard(): void
    {
        $admin = User::where('email', 'admin@documentalista.com.br')->first();

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertStatus(200);
        $response->assertSee('Documentalista Leads CRM');
    }

    public function test_dashboard_widgets_render_correctly(): void
    {
        $admin = User::where('email', 'admin@documentalista.com.br')->first();
        $this->actingAs($admin);

        Livewire::test(LeadStatsOverviewWidget::class)
            ->assertSuccessful()
            ->assertSee('Total de Leads Captados');

        Livewire::test(LeadFunnelChartWidget::class)
            ->assertSuccessful();

        Livewire::test(ChannelEngagementRateWidget::class)
            ->assertSuccessful()
            ->assertSee('Taxa de Leitura no WhatsApp');
    }
}
