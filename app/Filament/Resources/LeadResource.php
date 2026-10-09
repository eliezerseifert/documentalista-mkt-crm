<?php

namespace App\Filament\Resources;

use App\Enums\LeadStatus;
use App\Filament\Resources\LeadResource\Pages;
use App\Filament\Resources\LeadResource\RelationManagers\EventsRelationManager;
use App\Filament\Resources\LeadResource\RelationManagers\MessageLogsRelationManager;
use App\Models\Lead;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LeadResource extends Resource
{
    protected static ?string $model = Lead::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'CRM & Leads';
    protected static ?string $navigationLabel = 'Leads & Contatos';
    protected static ?string $modelLabel = 'Lead';
    protected static ?string $pluralModelLabel = 'Leads';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()->schema([
                    Forms\Components\Grid::make(3)->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nome Completo')
                            ->required(),

                        Forms\Components\TextInput::make('email')
                            ->label('E-mail')
                            ->email()
                            ->required(),

                        Forms\Components\TextInput::make('phone')
                            ->label('Telefone / WhatsApp (E.164)')
                            ->required(),
                    ]),

                    Forms\Components\Grid::make(3)->schema([
                        Forms\Components\Select::make('status')
                            ->label('Status do Lead')
                            ->options(collect(LeadStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()]))
                            ->required(),

                        Forms\Components\TextInput::make('score')
                            ->label('Lead Score')
                            ->numeric()
                            ->default(0),

                        Forms\Components\TextInput::make('saas_user_id')
                            ->label('ID do Usuário no SaaS'),
                    ]),

                    Forms\Components\Grid::make(2)->schema([
                        Forms\Components\DateTimePicker::make('trial_ends_at')
                            ->label('Término do Teste Grátis (15 dias)'),

                        Forms\Components\DateTimePicker::make('cycle_ends_at')
                            ->label('Término do Ciclo Total (30 dias)'),
                    ]),

                    Forms\Components\Section::make('Rastreamento de Origem (UTMs)')
                        ->schema([
                            Forms\Components\Grid::make(3)->schema([
                                Forms\Components\TextInput::make('utm_source')->label('UTM Source'),
                                Forms\Components\TextInput::make('utm_medium')->label('UTM Medium'),
                                Forms\Components\TextInput::make('utm_campaign')->label('UTM Campaign'),
                            ]),
                        ])
                        ->collapsible(),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('phone')
                    ->label('WhatsApp')
                    ->searchable()
                    ->url(fn (Lead $record): string => 'https://wa.me/' . ltrim($record->phone, '+'), true)
                    ->icon('heroicon-o-chat-bubble-left-ellipsis'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (LeadStatus $state): string => $state->label())
                    ->color(fn (LeadStatus $state): string => $state->color()),

                Tables\Columns\TextColumn::make('score')
                    ->label('Score')
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state >= 15 => 'success',
                        $state >= 5 => 'warning',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('days_remaining_trial')
                    ->label('Dias Trial')
                    ->suffix(' dias')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Cadastrado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Filtrar por Status')
                    ->options(collect(LeadStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()])),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Resumo do Lead')
                    ->schema([
                        Infolists\Components\Grid::make(4)->schema([
                            Infolists\Components\TextEntry::make('name')
                                ->label('Nome Completo')
                                ->weight('bold'),

                            Infolists\Components\TextEntry::make('email')
                                ->label('E-mail')
                                ->copyable(),

                            Infolists\Components\TextEntry::make('phone')
                                ->label('Telefone / WhatsApp')
                                ->url(fn (Lead $record): string => 'https://wa.me/' . ltrim($record->phone, '+'), true),

                            Infolists\Components\TextEntry::make('score')
                                ->label('Lead Score')
                                ->badge()
                                ->color('primary'),
                        ]),

                        Infolists\Components\Grid::make(4)->schema([
                            Infolists\Components\TextEntry::make('status')
                                ->label('Status do Ciclo')
                                ->badge()
                                ->formatStateUsing(fn (LeadStatus $state): string => $state->label())
                                ->color(fn (LeadStatus $state): string => $state->color()),

                            Infolists\Components\TextEntry::make('days_remaining_trial')
                                ->label('Dias Restantes no Teste')
                                ->suffix(' dias'),

                            Infolists\Components\TextEntry::make('trial_ends_at')
                                ->label('Fim do Trial')
                                ->dateTime('d/m/Y H:i'),

                            Infolists\Components\TextEntry::make('cycle_ends_at')
                                ->label('Fim do Ciclo (30 dias)')
                                ->dateTime('d/m/Y H:i'),
                        ]),
                    ]),

                Infolists\Components\Section::make('Atribuição e Marketing (UTMs)')
                    ->schema([
                        Infolists\Components\Grid::make(3)->schema([
                            Infolists\Components\TextEntry::make('utm_source')->label('Origem (UTM Source)')->default('-'),
                            Infolists\Components\TextEntry::make('utm_medium')->label('Meio (UTM Medium)')->default('-'),
                            Infolists\Components\TextEntry::make('utm_campaign')->label('Campanha (UTM Campaign)')->default('-'),
                        ]),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            EventsRelationManager::class,
            MessageLogsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLeads::route('/'),
            'create' => Pages\CreateLead::route('/create'),
            'view' => Pages\ViewLead::route('/{record}'),
            'edit' => Pages\EditLead::route('/{record}/edit'),
        ];
    }
}
