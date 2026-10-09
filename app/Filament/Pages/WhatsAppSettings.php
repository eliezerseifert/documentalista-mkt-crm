<?php

namespace App\Filament\Pages;

use App\Models\IntegrationSetting;
use App\Services\WhatsApp\WhatsAppCloudApiService;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\HtmlString;

class WhatsAppSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';
    protected static ?string $navigationGroup = 'Integrações';
    protected static ?string $navigationLabel = 'WhatsApp Business';
    protected static ?string $title = 'Integração: WhatsApp Business (Meta Cloud API)';
    protected static string $view = 'filament.pages.whats-app-settings';

    public ?array $data = [];
    public ?string $testPhoneNumber = '';
    public ?string $testMessage = 'Olá! Esta é uma mensagem de teste enviada pela Automação Documentalista Leads.';

    public function mount(): void
    {
        $settings = IntegrationSetting::get('whatsapp_cloud', [
            'phone_number_id' => '',
            'waba_id' => '',
            'access_token' => '',
            'verify_token' => 'doc_wpp_verify_' . substr(md5(config('app.key')), 0, 16),
            'api_version' => 'v20.0',
        ]);

        $this->form->fill($settings);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Credenciais da Meta Graph API')
                    ->description('Configurações obtidas no Meta for Developers (Painel do WhatsApp Cloud API).')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('phone_number_id')
                                ->label('Phone Number ID')
                                ->placeholder('Ex: 104829104812345')
                                ->required(),

                            TextInput::make('waba_id')
                                ->label('WhatsApp Business Account ID (WABA ID)')
                                ->placeholder('Ex: 294810294810294')
                                ->required(),

                            TextInput::make('access_token')
                                ->label('Permanent Access Token')
                                ->password()
                                ->revealable()
                                ->helperText('Armazenado com criptografia AES-256 no banco de dados.')
                                ->required()
                                ->columnSpanFull(),

                            TextInput::make('verify_token')
                                ->label('Verify Token do Webhook')
                                ->helperText('Token secreto utilizado pela Meta para validar o Webhook no painel developers.')
                                ->required(),

                            TextInput::make('api_version')
                                ->label('Versão da Graph API')
                                ->default('v20.0')
                                ->required(),
                        ]),
                    ]),

                Section::make('Configuração do Webhook na Meta')
                    ->schema([
                        Placeholder::make('webhook_callback_url')
                            ->label('Callback URL (URL de retorno)')
                            ->content(new HtmlString('<code>' . url('/api/webhooks/whatsapp') . '</code>')),

                        Placeholder::make('webhook_fields')
                            ->label('Campos obrigatórios para assinar na Meta')
                            ->content(new HtmlString('Marque as opções: <strong>messages</strong> (recebe mensagens e respostas) e <strong>message_deliveries / message_status</strong> (recebe status de entrega e leitura).')),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();
        IntegrationSetting::set('whatsapp_cloud', $state);

        Notification::make()
            ->title('Credenciais do WhatsApp salvas com sucesso!')
            ->success()
            ->send();
    }

    public function testConnection(): void
    {
        $this->save();

        $service = app(WhatsAppCloudApiService::class);
        $result = $service->testConnection();

        if ($result['success']) {
            $details = $result['details'] ?? [];
            $name = $details['verified_name'] ?? 'Nome não verificado';
            $phone = $details['display_phone_number'] ?? 'Sem número';
            $quality = $details['quality_rating'] ?? 'GREEN';

            Notification::make()
                ->title('Conexão com a Meta bem-sucedida! 🟢')
                ->body("Conta: {$name} | Número: {$phone} | Qualidade: {$quality}")
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Falha ao conectar à Meta')
                ->body($result['message'])
                ->danger()
                ->send();
        }
    }

    public function sendTestMessage(): void
    {
        if (empty($this->testPhoneNumber)) {
            Notification::make()
                ->title('Informe o número de telefone de destino com DDD.')
                ->warning()
                ->send();
            return;
        }

        $service = app(WhatsAppCloudApiService::class);
        $result = $service->sendTextMessage($this->testPhoneNumber, $this->testMessage);

        if ($result['success']) {
            Notification::make()
                ->title('Mensagem de teste enviada com sucesso!')
                ->body('Message ID retornado pela Meta: ' . ($result['message_id'] ?? 'OK'))
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Erro ao enviar mensagem')
                ->body($result['error'] ?? 'Falha desconhecida.')
                ->danger()
                ->send();
        }
    }
}
