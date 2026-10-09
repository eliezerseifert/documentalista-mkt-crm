<?php

namespace App\Filament\Resources\LeadResource\RelationManagers;

use App\Enums\MessageDeliveryStatus;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class MessageLogsRelationManager extends RelationManager
{
    protected static string $relationship = 'messageLogs';
    protected static ?string $title = 'Histórico de Mensagens Disparadas';
    protected static ?string $modelLabel = 'Mensagem';
    protected static ?string $pluralModelLabel = 'Mensagens';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('channel')
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Disparada em')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable(),

                Tables\Columns\TextColumn::make('channel')
                    ->label('Canal')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'whatsapp' => 'success',
                        'email' => 'info',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('direction')
                    ->label('Direção')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state === 'inbound' ? 'Recebida' : 'Enviada')
                    ->color(fn ($state) => $state === 'inbound' ? 'purple' : 'gray'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status de Entrega')
                    ->badge()
                    ->formatStateUsing(fn (MessageDeliveryStatus $state): string => $state->label())
                    ->color(fn (MessageDeliveryStatus $state): string => $state->color()),

                Tables\Columns\TextColumn::make('flowStep.step_number')
                    ->label('Etapa #')
                    ->default('-'),

                Tables\Columns\TextColumn::make('content_sent')
                    ->label('Conteúdo')
                    ->wrap()
                    ->limit(80),

                Tables\Columns\TextColumn::make('external_message_id')
                    ->label('ID Meta / Mailer')
                    ->fontFamily('mono')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ]);
    }
}
