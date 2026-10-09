<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WebhookLogResource\Pages;
use App\Models\WebhookLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class WebhookLogResource extends Resource
{
    protected static ?string $model = WebhookLog::class;
    protected static ?string $navigationIcon = 'heroicon-o-queue-list';
    protected static ?string $navigationGroup = 'Integrações';
    protected static ?string $navigationLabel = 'Logs de Webhooks';
    protected static ?string $modelLabel = 'Log de Webhook';
    protected static ?string $pluralModelLabel = 'Logs de Webhooks';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(3)->schema([
                    Forms\Components\TextInput::make('origin')->label('Origem')->disabled(),
                    Forms\Components\TextInput::make('ip_address')->label('Endereço IP')->disabled(),
                    Forms\Components\TextInput::make('response_status')->label('Status HTTP')->disabled(),
                ]),
                Forms\Components\Textarea::make('error_message')
                    ->label('Mensagem de Erro')
                    ->disabled()
                    ->columnSpanFull(),
                Forms\Components\KeyValue::make('headers')
                    ->label('Cabeçalhos HTTP (Headers)')
                    ->disabled()
                    ->columnSpanFull(),
                Forms\Components\KeyValue::make('payload')
                    ->label('Payload Recebido')
                    ->disabled()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('#')->sortable(),
                Tables\Columns\TextColumn::make('created_at')->label('Data / Hora')->dateTime('d/m/Y H:i:s')->sortable(),
                Tables\Columns\TextColumn::make('origin')->label('Origem')->badge(),
                Tables\Columns\TextColumn::make('response_status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (int $state): string => $state >= 200 && $state < 300 ? 'success' : 'danger'),
                Tables\Columns\TextColumn::make('ip_address')->label('IP')->fontFamily('mono'),
                Tables\Columns\TextColumn::make('error_message')->label('Erro')->limit(50)->color('danger'),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('origin')
                    ->label('Filtrar Origem')
                    ->options([
                        'form_capture' => 'Formulário de Captação',
                        'whatsapp' => 'WhatsApp Cloud API',
                        'subscription_checkout' => 'Checkout / Assinatura',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWebhookLogs::route('/'),
        ];
    }
}
