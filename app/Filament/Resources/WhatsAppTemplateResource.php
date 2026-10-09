<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WhatsAppTemplateResource\Pages;
use App\Models\WhatsAppTemplate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class WhatsAppTemplateResource extends Resource
{
    protected static ?string $model = WhatsAppTemplate::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-duplicate';
    protected static ?string $navigationGroup = 'Integrações';
    protected static ?string $navigationLabel = 'Templates WhatsApp';
    protected static ?string $modelLabel = 'Template de WhatsApp';
    protected static ?string $pluralModelLabel = 'Templates de WhatsApp';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nome do Template na Meta')
                    ->placeholder('ex: lead_trial_step_1')
                    ->required()
                    ->unique(ignoreRecord: true),

                Forms\Components\TextInput::make('language')
                    ->label('Código do Idioma')
                    ->default('pt_BR')
                    ->required(),

                Forms\Components\Select::make('category')
                    ->label('Categoria')
                    ->options([
                        'MARKETING' => 'Marketing',
                        'UTILITY' => 'Utilidade',
                        'AUTHENTICATION' => 'Autenticação',
                    ])
                    ->default('MARKETING')
                    ->required(),

                Forms\Components\Select::make('status')
                    ->label('Status na Meta')
                    ->options([
                        'APPROVED' => 'Aprovado (APPROVED)',
                        'PENDING' => 'Pendente (PENDING)',
                        'REJECTED' => 'Rejeitado (REJECTED)',
                    ])
                    ->default('APPROVED')
                    ->required(),

                Forms\Components\Textarea::make('body_preview')
                    ->label('Texto / Prévia do Template')
                    ->rows(4)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome do Template')
                    ->searchable()
                    ->sortable()
                    ->fontFamily('mono'),

                Tables\Columns\TextColumn::make('category')
                    ->label('Categoria')
                    ->badge(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'APPROVED' => 'success',
                        'PENDING' => 'warning',
                        'REJECTED' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('language')
                    ->label('Idioma'),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Última Atualização')
                    ->dateTime('d/m/Y H:i'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWhatsAppTemplates::route('/'),
            'create' => Pages\CreateWhatsAppTemplate::route('/create'),
            'edit' => Pages\EditWhatsAppTemplate::route('/{record}/edit'),
        ];
    }
}
