<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FlowResource\Pages;
use App\Filament\Resources\FlowResource\RelationManagers\StepsRelationManager;
use App\Models\Flow;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FlowResource extends Resource
{
    protected static ?string $model = Flow::class;
    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';
    protected static ?string $navigationGroup = 'Automação';
    protected static ?string $navigationLabel = 'Fluxos de Cadência';
    protected static ?string $modelLabel = 'Fluxo de Automação';
    protected static ?string $pluralModelLabel = 'Fluxos de Automação';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()->schema([
                    Forms\Components\Grid::make(2)->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nome do Fluxo')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('slug')
                            ->label('Identificador (Slug)')
                            ->required()
                            ->unique(ignoreRecord: true),

                        Forms\Components\TextInput::make('total_days')
                            ->label('Duração Total (Dias)')
                            ->numeric()
                            ->default(15)
                            ->required(),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Fluxo Ativo')
                            ->helperText('Se desativado, o agendador não disparará mensagens para este fluxo.')
                            ->default(true),

                        Forms\Components\Textarea::make('description')
                            ->label('Descrição do Objetivo do Fluxo')
                            ->columnSpanFull(),
                    ]),
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

                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('total_days')
                    ->label('Duração')
                    ->suffix(' dias')
                    ->sortable(),

                Tables\Columns\TextColumn::make('steps_count')
                    ->counts('steps')
                    ->label('Etapas Cadastradas')
                    ->badge()
                    ->color('info'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Ativo')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            StepsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFlows::route('/'),
            'create' => Pages\CreateFlow::route('/create'),
            'edit' => Pages\EditFlow::route('/{record}/edit'),
        ];
    }
}
