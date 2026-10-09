<?php

namespace App\Filament\Resources\LeadResource\Pages;

use App\Enums\LeadEventType;
use App\Enums\LeadStatus;
use App\Filament\Resources\LeadResource;
use App\Models\Lead;
use App\Models\LeadEvent;
use App\Services\Lead\SubscriptionService;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewLead extends ViewRecord
{
    protected static string $resource = LeadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('registerSubscription')
                ->label('Registrar Assinatura Manual')
                ->icon('heroicon-o-sparkles')
                ->color('success')
                ->visible(fn (Lead $record): bool => $record->status !== LeadStatus::Assinou)
                ->form([
                    Forms\Components\TextInput::make('plan_name')
                        ->label('Nome do Plano')
                        ->default('Mensal Pro')
                        ->required(),

                    Forms\Components\TextInput::make('amount')
                        ->label('Valor Pago (R$)')
                        ->numeric()
                        ->default(197.00)
                        ->required(),

                    Forms\Components\TextInput::make('transaction_id')
                        ->label('Código da Transação')
                        ->placeholder('manual_' . time()),
                ])
                ->action(function (Lead $record, array $data) {
                    $service = app(SubscriptionService::class);
                    $service->recordConversion($record, $data);

                    Notification::make()
                        ->title('Assinatura registrada e lead convertido com sucesso!')
                        ->success()
                        ->send();
                }),

            Actions\Action::make('toggleFlowPause')
                ->label(fn (Lead $record): string => $record->activeFlowRun?->status === 'paused' ? 'Retomar Fluxo' : 'Pausar Fluxo')
                ->icon('heroicon-o-pause-circle')
                ->color('warning')
                ->visible(fn (Lead $record): bool => in_array($record->status, [LeadStatus::EmFluxo1, LeadStatus::EmFluxo2]))
                ->action(function (Lead $record) {
                    $activeRun = $record->activeFlowRun;
                    if ($activeRun) {
                        $isPaused = $activeRun->status === 'paused';
                        $activeRun->update([
                            'status' => $isPaused ? 'active' : 'paused',
                            'paused_at' => $isPaused ? null : now(),
                        ]);

                        LeadEvent::create([
                            'lead_id' => $record->id,
                            'event_type' => LeadEventType::MensagemEnfileirada,
                            'title' => $isPaused ? 'Fluxo Retomado' : 'Fluxo Pausado Manualmente',
                            'description' => $isPaused ? 'Envios automáticos reativados.' : 'Envios temporariamente suspensos.',
                            'occurred_at' => now(),
                        ]);

                        Notification::make()
                            ->title($isPaused ? 'Fluxo retomado com sucesso!' : 'Fluxo pausado com sucesso!')
                            ->success()
                            ->send();
                    }
                }),

            Actions\EditAction::make(),

            Actions\DeleteAction::make()
                ->label('Excluir Dados do Lead (LGPD)')
                ->modalHeading('Exclusão de Dados Pessoais (LGPD)')
                ->modalDescription('Esta ação removerá permanentemente o lead, suas mensagens, eventos e histórico de rastreamento do banco de dados.'),
        ];
    }
}
