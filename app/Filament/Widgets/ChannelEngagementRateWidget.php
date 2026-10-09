<?php

namespace App\Filament\Widgets;

use App\Enums\LeadEventType;
use App\Enums\MessageDeliveryStatus;
use App\Models\LeadEvent;
use App\Models\MessageLog;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ChannelEngagementRateWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected function getStats(): array
    {
        // WhatsApp
        $wppTotal = MessageLog::where('channel', 'whatsapp')->where('direction', 'outbound')->count();
        $wppRead = MessageLog::where('channel', 'whatsapp')->where('status', MessageDeliveryStatus::Lida)->count();
        $wppRate = $wppTotal > 0 ? number_format(($wppRead / $wppTotal) * 100, 1) : '0.0';

        // E-mail
        $emailTotal = MessageLog::where('channel', 'email')->where('direction', 'outbound')->count();
        $emailOpens = LeadEvent::where('event_type', LeadEventType::EmailAberto)->count();
        $emailRate = $emailTotal > 0 ? number_format(($emailOpens / $emailTotal) * 100, 1) : '0.0';

        // Cliques
        $totalClicks = LeadEvent::where('event_type', LeadEventType::LinkClicado)->count();
        $totalCheckoutVisits = LeadEvent::where('event_type', LeadEventType::ChegouCheckout)->count();

        return [
            Stat::make('Taxa de Leitura no WhatsApp', "{$wppRate}%")
                ->description("{$wppRead} de {$wppTotal} mensagens confirmadas como lidas")
                ->descriptionIcon('heroicon-m-chat-bubble-bottom-center-text')
                ->color('success'),

            Stat::make('Taxa de Abertura de E-mails', "{$emailRate}%")
                ->description("{$emailOpens} aberturas rastreadas via pixel 1x1")
                ->descriptionIcon('heroicon-m-envelope-open')
                ->color('info'),

            Stat::make('Cliques Rastreáveis no Checkout', (string) $totalCheckoutVisits)
                ->description("{$totalClicks} cliques totais em botões e links")
                ->descriptionIcon('heroicon-m-cursor-arrow-rays')
                ->color('warning'),
        ];
    }
}
