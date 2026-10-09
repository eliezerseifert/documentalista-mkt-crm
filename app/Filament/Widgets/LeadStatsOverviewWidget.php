<?php

namespace App\Filament\Widgets;

use App\Enums\LeadStatus;
use App\Models\Lead;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class LeadStatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $totalLeads = Lead::count();
        $inTrial = Lead::where('status', LeadStatus::EmFluxo1)->count();
        $engaged = Lead::where('status', LeadStatus::Engajado)->count();
        $subscribed = Lead::where('status', LeadStatus::Assinou)->count();

        $conversionRate = $totalLeads > 0 
            ? number_format(($subscribed / $totalLeads) * 100, 1) 
            : '0.0';

        return [
            Stat::make('Total de Leads Captados', (string) $totalLeads)
                ->description('Origem: app.documentalista.com.br/register')
                ->descriptionIcon('heroicon-m-user-plus')
                ->chart([$totalLeads, $totalLeads + 2, $totalLeads + 5])
                ->color('primary'),

            Stat::make('Em Período de Teste (Fluxo 1)', (string) $inTrial)
                ->description('15 dias de teste grátis ativos')
                ->descriptionIcon('heroicon-m-clock')
                ->color('info'),

            Stat::make('Leads Altamente Engajados', (string) $engaged)
                ->description('Abriram, clicaram ou foram ao checkout')
                ->descriptionIcon('heroicon-m-fire')
                ->color('warning'),

            Stat::make('Assinantes Convertidos', (string) $subscribed)
                ->description("Taxa de Conversão: {$conversionRate}%")
                ->descriptionIcon('heroicon-m-sparkles')
                ->color('success'),
        ];
    }
}
