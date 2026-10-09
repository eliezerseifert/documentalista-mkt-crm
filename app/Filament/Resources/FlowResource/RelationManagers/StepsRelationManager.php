<?php

namespace App\Filament\Resources\FlowResource\RelationManagers;

use App\Enums\FlowChannel;
use App\Mail\DynamicCampaignMail;
use App\Models\FlowStep;
use App\Models\Lead;
use App\Models\StepMessage;
use App\Services\Automation\MessageVariableParserService;
use App\Services\Email\DynamicSmtpTransportFactory;
use App\Services\WhatsApp\WhatsAppCloudApiService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;

class StepsRelationManager extends RelationManager
{
    protected static string $relationship = 'steps';
    protected static ?string $title = 'Etapas da Cadência (Steps)';
    protected static ?string $modelLabel = 'Etapa';
    protected static ?string $pluralModelLabel = 'Etapas';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Etapa')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Configurações da Etapa')
                            ->icon('heroicon-o-cog-6-tooth')
                            ->schema([
                                Forms\Components\Grid::make(3)->schema([
                                    Forms\Components\TextInput::make('step_number')
                                        ->label('Ordem da Etapa (#)')
                                        ->numeric()
                                        ->required(),

                                    Forms\Components\TextInput::make('day_offset')
                                        ->label('Dia do Ciclo (Offset)')
                                        ->helperText('0 = no dia da inscrição, 1 = dia seguinte...')
                                        ->numeric()
                                        ->required(),

                                    Forms\Components\TimePicker::make('send_time')
                                        ->label('Horário de Disparo')
                                        ->default('09:00:00')
                                        ->required(),
                                ]),

                                Forms\Components\Grid::make(2)->schema([
                                    Forms\Components\Select::make('channel')
                                        ->label('Canal de Comunicação')
                                        ->options([
                                            FlowChannel::WhatsApp->value => 'Apenas WhatsApp',
                                            FlowChannel::Email->value => 'Apenas E-mail',
                                            FlowChannel::Ambos->value => 'WhatsApp & E-mail (Ambos)',
                                        ])
                                        ->default(FlowChannel::Ambos->value)
                                        ->required(),

                                    Forms\Components\Toggle::make('is_active')
                                        ->label('Etapa Ativa')
                                        ->default(true),
                                ]),

                                Forms\Components\Section::make('Oferta / Promoção Especial')
                                    ->schema([
                                        Forms\Components\Grid::make(3)->schema([
                                            Forms\Components\Toggle::make('is_promotion')
                                                ->label('É uma etapa com cupom promocional?')
                                                ->reactive(),

                                            Forms\Components\TextInput::make('promo_coupon_code')
                                                ->label('Código do Cupom')
                                                ->placeholder('ex: DOC50OFF')
                                                ->visible(fn ($get) => (bool) $get('is_promotion')),

                                            Forms\Components\TextInput::make('promo_expires_in_days')
                                                ->label('Validade da Oferta (Dias)')
                                                ->numeric()
                                                ->placeholder('ex: 2')
                                                ->visible(fn ($get) => (bool) $get('is_promotion')),
                                        ]),
                                    ]),
                            ]),

                        Forms\Components\Tabs\Tab::make('Mensagem WhatsApp')
                            ->icon('heroicon-o-chat-bubble-left-ellipsis')
                            ->schema([
                                Forms\Components\Placeholder::make('wpp_tags')
                                    ->label('Variáveis Disponíveis')
                                    ->content('{{nome}}, {{primeiro_nome}}, {{email}}, {{dias_restantes_trial}}, {{link_checkout}}, {{cupom}}'),

                                Forms\Components\Textarea::make('whatsapp_body')
                                    ->label('Texto da Mensagem (WhatsApp)')
                                    ->rows(5)
                                    ->afterStateHydrated(function ($component, $record) {
                                        if ($record) {
                                            $msg = $record->whatsappMessage;
                                            $component->state($msg?->body_text);
                                        }
                                    }),

                                Forms\Components\Grid::make(2)->schema([
                                    Forms\Components\TextInput::make('whatsapp_button_label')
                                        ->label('Rótulo do Botão Interativo (CTA)')
                                        ->placeholder('ex: Acessar Checkout')
                                        ->afterStateHydrated(function ($component, $record) {
                                            if ($record) {
                                                $msg = $record->whatsappMessage;
                                                $component->state($msg?->button_label);
                                            }
                                        }),

                                    Forms\Components\TextInput::make('whatsapp_button_url')
                                        ->label('URL do Botão (ou use {{link_checkout}})')
                                        ->placeholder('https://checkout.v2.e-flips.com.br ou {{link_checkout}}')
                                        ->afterStateHydrated(function ($component, $record) {
                                            if ($record) {
                                                $msg = $record->whatsappMessage;
                                                $component->state($msg?->button_url);
                                            }
                                        }),
                                ]),

                                Forms\Components\TextInput::make('whatsapp_template_name')
                                    ->label('Nome do Template Meta (Para disparos fora da janela 24h)')
                                    ->placeholder('ex: lead_trial_step_1')
                                    ->afterStateHydrated(function ($component, $record) {
                                        if ($record) {
                                            $msg = $record->whatsappMessage;
                                            $component->state($msg?->whatsapp_template_name);
                                        }
                                    }),
                            ]),

                        Forms\Components\Tabs\Tab::make('Mensagem E-mail')
                            ->icon('heroicon-o-envelope')
                            ->schema([
                                Forms\Components\TextInput::make('email_subject')
                                    ->label('Assunto do E-mail')
                                    ->afterStateHydrated(function ($component, $record) {
                                        if ($record) {
                                            $msg = $record->emailMessage;
                                            $component->state($msg?->subject);
                                        }
                                    }),

                                Forms\Components\RichEditor::make('email_body')
                                    ->label('Corpo do E-mail (HTML Responsivo)')
                                    ->afterStateHydrated(function ($component, $record) {
                                        if ($record) {
                                            $msg = $record->emailMessage;
                                            $component->state($msg?->body_text);
                                        }
                                    }),

                                Forms\Components\Grid::make(2)->schema([
                                    Forms\Components\TextInput::make('email_button_label')
                                        ->label('Texto do Botão CTA do E-mail')
                                        ->placeholder('ex: Ativar Minha Assinatura')
                                        ->afterStateHydrated(function ($component, $record) {
                                            if ($record) {
                                                $msg = $record->emailMessage;
                                                $component->state($msg?->button_label);
                                            }
                                        }),

                                    Forms\Components\TextInput::make('email_button_url')
                                        ->label('Destino do Botão (ou use {{link_checkout}})')
                                        ->placeholder('{{link_checkout}}')
                                        ->afterStateHydrated(function ($component, $record) {
                                            if ($record) {
                                                $msg = $record->emailMessage;
                                                $component->state($msg?->button_url);
                                            }
                                        }),
                                ]),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('step_number')
            ->defaultSort('step_number', 'asc')
            ->columns([
                Tables\Columns\TextColumn::make('step_number')
                    ->label('#')
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('day_offset')
                    ->label('Dia (Offset)')
                    ->suffix('º dia')
                    ->sortable(),

                Tables\Columns\TextColumn::make('send_time')
                    ->label('Horário')
                    ->time('H:i'),

                Tables\Columns\TextColumn::make('channel')
                    ->label('Canal')
                    ->badge(),

                Tables\Columns\IconColumn::make('is_promotion')
                    ->label('Promoção')
                    ->boolean(),

                Tables\Columns\TextColumn::make('promo_coupon_code')
                    ->label('Cupom')
                    ->badge()
                    ->color('warning')
                    ->default('-'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Ativo')
                    ->boolean(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Adicionar Etapa')
                    ->after(function (FlowStep $record, array $data) {
                        $this->syncStepMessages($record, $data);
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->after(function (FlowStep $record, array $data) {
                        $this->syncStepMessages($record, $data);
                    }),

                Tables\Actions\Action::make('sendTest')
                    ->label('Enviar Teste')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->form([
                        Forms\Components\TextInput::make('test_phone')
                            ->label('Número WhatsApp para Teste')
                            ->placeholder('11988887777'),

                        Forms\Components\TextInput::make('test_email')
                            ->label('E-mail para Teste')
                            ->email()
                            ->placeholder('teste@documentalista.com.br'),
                    ])
                    ->action(function (FlowStep $record, array $data) {
                        $this->dispatchStepTest($record, $data);
                    }),

                Tables\Actions\DeleteAction::make(),
            ]);
    }

    protected function syncStepMessages(FlowStep $step, array $data): void
    {
        // WhatsApp
        if (!empty($data['whatsapp_body'])) {
            StepMessage::updateOrCreate(
                [
                    'flow_step_id' => $step->id,
                    'channel' => 'whatsapp',
                ],
                [
                    'body_text' => $data['whatsapp_body'],
                    'button_label' => $data['whatsapp_button_label'] ?? null,
                    'button_url' => $data['whatsapp_button_url'] ?? null,
                    'whatsapp_template_name' => $data['whatsapp_template_name'] ?? null,
                ]
            );
        }

        // E-mail
        if (!empty($data['email_body']) || !empty($data['email_subject'])) {
            StepMessage::updateOrCreate(
                [
                    'flow_step_id' => $step->id,
                    'channel' => 'email',
                ],
                [
                    'subject' => $data['email_subject'] ?? 'Documentalista Novidades',
                    'body_text' => $data['email_body'] ?? '',
                    'button_label' => $data['email_button_label'] ?? null,
                    'button_url' => $data['email_button_url'] ?? null,
                ]
            );
        }
    }

    protected function dispatchStepTest(FlowStep $step, array $data): void
    {
        // Mock Lead temporário para parse de variáveis
        $mockLead = new Lead([
            'id' => 999999,
            'name' => 'Usuário de Demonstração',
            'email' => $data['test_email'] ?? 'teste@documentalista.com.br',
            'phone' => $data['test_phone'] ?? '+5511999999999',
            'trial_ends_at' => now()->addDays(7),
        ]);

        $parser = app(MessageVariableParserService::class);

        // Disparo WhatsApp se fornecido
        if (!empty($data['test_phone']) && $step->whatsappMessage) {
            $wppService = app(WhatsAppCloudApiService::class);
            $parsed = $parser->parse($step->whatsappMessage->body_text, $mockLead, $step);
            $wppService->sendTextMessage($data['test_phone'], $parsed);
        }

        // Disparo E-mail se fornecido
        if (!empty($data['test_email']) && $step->emailMessage) {
            $smtpFactory = app(DynamicSmtpTransportFactory::class);
            $smtpFactory->configureMailer();

            $subject = $parser->parse($step->emailMessage->subject ?? 'Teste Documentalista', $mockLead, $step);
            $body = $parser->parse($step->emailMessage->body_text, $mockLead, $step);

            $mailable = new DynamicCampaignMail(
                lead: $mockLead,
                mailSubject: $subject,
                bodyHtml: $body,
                buttonLabel: $step->emailMessage->button_label,
                buttonUrl: $step->emailMessage->button_url ? $parser->parse($step->emailMessage->button_url, $mockLead, $step) : null,
            );

            Mail::to($data['test_email'])->send($mailable);
        }

        Notification::make()
            ->title('Teste da etapa disparado com sucesso!')
            ->success()
            ->send();
    }
}
