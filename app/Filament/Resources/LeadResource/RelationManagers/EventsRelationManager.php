<?php

namespace App\Filament\Resources\LeadResource\RelationManagers;

use App\Enums\LeadEventType;
use App\Models\LeadEvent;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class EventsRelationManager extends RelationManager
{
    protected static string $relationship = 'events';
    protected static ?string $title = 'Timeline de Atividades do Lead';
    protected static ?string $modelLabel = 'Evento';
    protected static ?string $pluralModelLabel = 'Eventos';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->defaultSort('occurred_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('occurred_at')
                    ->label('Data / Hora')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('event_type')
                    ->label('Tipo de Evento')
                    ->badge()
                    ->formatStateUsing(fn (LeadEventType $state): string => $state->label())
                    ->color(fn (LeadEventType $state): string => $state->color())
                    ->icon(fn (LeadEventType $state): string => $state->icon()),

                Tables\Columns\TextColumn::make('channel')
                    ->label('Canal')
                    ->badge()
                    ->default('-'),

                Tables\Columns\TextColumn::make('title')
                    ->label('Ação / Título')
                    ->searchable(),

                Tables\Columns\TextColumn::make('description')
                    ->label('Detalhes')
                    ->wrap()
                    ->limit(100),

                Tables\Columns\TextColumn::make('score_delta')
                    ->label('Pontos')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state > 0 ? "+{$state}" : (string)$state)
                    ->color(fn ($state) => $state > 0 ? 'success' : 'gray'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('event_type')
                    ->label('Filtrar por Tipo')
                    ->options(collect(LeadEventType::cases())->mapWithKeys(fn ($e) => [$e->value => $e->label()])),
            ]);
    }
}
