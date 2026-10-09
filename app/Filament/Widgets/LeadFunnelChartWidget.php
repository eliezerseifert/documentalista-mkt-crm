<?php

namespace App\Filament\Widgets;

use App\Enums\LeadEventType;
use App\Models\Lead;
use App\Models\LeadEvent;
use Filament\Widgets\ChartWidget;

class LeadFunnelChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Funil de Conversão (Jornada Completa do Lead)';
    protected static ?int $sort = 2;
    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $cadastros = Lead::count();
        $entregues = LeadEvent::where('event_type', LeadEventType::MensagemEntregue)->distinct('lead_id')->count('lead_id');
        $abertosLidos = LeadEvent::whereIn('event_type', [LeadEventType::EmailAberto, LeadEventType::MensagemLida])->distinct('lead_id')->count('lead_id');
        $clicaram = LeadEvent::where('event_type', LeadEventType::LinkClicado)->distinct('lead_id')->count('lead_id');
        $checkout = LeadEvent::where('event_type', LeadEventType::ChegouCheckout)->distinct('lead_id')->count('lead_id');
        $assinaram = LeadEvent::where('event_type', LeadEventType::Assinou)->distinct('lead_id')->count('lead_id');

        return [
            'datasets' => [
                [
                    'label' => 'Total de Leads Únicos',
                    'data' => [
                        $cadastros,
                        $entregues,
                        $abertosLidos,
                        $clicaram,
                        $checkout,
                        $assinaram,
                    ],
                    'backgroundColor' => [
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(14, 165, 233, 0.8)',
                        'rgba(168, 85, 247, 0.8)',
                        'rgba(245, 158, 11, 0.8)',
                        'rgba(249, 115, 22, 0.8)',
                        'rgba(34, 197, 94, 0.8)',
                    ],
                    'borderRadius' => 6,
                ],
            ],
            'labels' => [
                '1. Cadastros',
                '2. Msg Entregue',
                '3. Aberto / Lido',
                '4. Link Clicado',
                '5. Checkout',
                '6. Assinatura 🎉',
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
