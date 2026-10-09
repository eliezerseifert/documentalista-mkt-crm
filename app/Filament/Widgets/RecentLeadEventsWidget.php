<?php

namespace App\Filament\Widgets;

use App\Enums\LeadEventType;
use App\Models\LeadEvent;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentLeadEventsWidget extends BaseWidget
{
    protected static ?string $heading = 'Últimas Atividades e Eventos de Leads em Tempo Real';
    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                LeadEvent::with('lead')->latest('occurred_at')->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('occurred_at')
                    ->label('Horário')
                    ->dateTime('d/m/Y H:i:s')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('lead.name')
                    ->label('Lead')
                    ->searchable()
                    ->url(fn (LeadEvent $record) => $record->lead ? route('filament.admin.resources.leads.view', $record->lead) : null)
                    ->color('primary'),

                Tables\Columns\TextColumn::make('event_type')
                    ->label('Tipo de Evento')
                    ->badge()
                    ->formatStateUsing(fn (LeadEventType $state): string => $state->label())
                    ->color(fn (LeadEventType $state): string => $state->color())
                    ->icon(fn (LeadEventType $state): string => $state->icon()),

                Tables\Columns\TextColumn::make('title')
                    ->label('Título da Ação'),

                Tables\Columns\TextColumn::make('description')
                    ->label('Detalhes')
                    ->limit(60),

                Tables\Columns\TextColumn::make('score_delta')
                    ->label('Pontos')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state > 0 ? "+{$state}" : (string) $state)
                    ->color(fn ($state) => $state > 0 ? 'success' : 'gray'),
            ]);
    }
}
