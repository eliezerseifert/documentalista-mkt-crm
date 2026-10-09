<?php

namespace App\Filament\Pages;

use App\Mail\TestSmtpMail;
use App\Models\IntegrationSetting;
use App\Services\Email\DynamicSmtpTransportFactory;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Mail;

class SmtpSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';
    protected static ?string $navigationGroup = 'Integrações';
    protected static ?string $navigationLabel = 'E-mail (SMTP)';
    protected static ?string $title = 'Integração: Servidor de E-mail (SMTP)';
    protected static string $view = 'filament.pages.smtp-settings';

    public ?array $data = [];
    public ?string $testRecipientEmail = '';

    public function mount(): void
    {
        $settings = IntegrationSetting::get('smtp', [
            'host' => 'smtp.mailtrap.io',
            'port' => 587,
            'encryption' => 'tls',
            'username' => '',
            'password' => '',
            'from_name' => 'Documentalista',
            'from_email' => 'contato@documentalista.com.br',
            'reply_to' => 'suporte@documentalista.com.br',
        ]);

        $this->form->fill($settings);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Parâmetros do Servidor SMTP')
                    ->description('Configurações aplicadas dinamicamente sem necessidade de alterar o arquivo .env.')
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('host')
                                ->label('Host do Servidor SMTP')
                                ->placeholder('ex: smtp.sendgrid.net ou email-smtp.us-east-1.amazonaws.com')
                                ->required()
                                ->columnSpan(2),

                            TextInput::make('port')
                                ->label('Porta')
                                ->numeric()
                                ->default(587)
                                ->required(),
                        ]),

                        Grid::make(3)->schema([
                            Select::make('encryption')
                                ->label('Criptografia')
                                ->options([
                                    'tls' => 'STARTTLS (Recomendado)',
                                    'ssl' => 'SSL',
                                    'none' => 'Nenhuma',
                                ])
                                ->default('tls')
                                ->required(),

                            TextInput::make('username')
                                ->label('Usuário SMTP')
                                ->placeholder('ex: apikey ou usuario@seudominio.com'),

                            TextInput::make('password')
                                ->label('Senha SMTP')
                                ->password()
                                ->revealable()
                                ->helperText('Armazenado criptografado no banco.'),
                        ]),
                    ]),

                Section::make('Identidade do Remetente')
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('from_name')
                                ->label('Nome do Remetente')
                                ->default('Documentalista')
                                ->required(),

                            TextInput::make('from_email')
                                ->label('E-mail do Remetente')
                                ->email()
                                ->default('contato@documentalista.com.br')
                                ->required(),

                            TextInput::make('reply_to')
                                ->label('Responder Para (Reply-To)')
                                ->email()
                                ->placeholder('suporte@documentalista.com.br'),
                        ]),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();
        IntegrationSetting::set('smtp', $state);

        Notification::make()
            ->title('Configurações SMTP salvas com sucesso!')
            ->success()
            ->send();
    }

    public function testConnection(): void
    {
        $this->save();

        $service = app(DynamicSmtpTransportFactory::class);
        $result = $service->testConnection();

        if ($result['success']) {
            Notification::make()
                ->title('Conexão SMTP estabelecida com sucesso! 🟢')
                ->body($result['message'])
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Falha de conexão com o servidor SMTP')
                ->body($result['message'])
                ->danger()
                ->send();
        }
    }

    public function sendTestEmail(): void
    {
        $this->save();

        if (empty($this->testRecipientEmail) || !filter_var($this->testRecipientEmail, FILTER_VALIDATE_EMAIL)) {
            Notification::make()
                ->title('Informe um e-mail de destino válido.')
                ->warning()
                ->send();
            return;
        }

        try {
            $factory = app(DynamicSmtpTransportFactory::class);
            $factory->configureMailer();

            Mail::mailer('dynamic_smtp')->to($this->testRecipientEmail)->send(new TestSmtpMail($this->testRecipientEmail));

            Notification::make()
                ->title('E-mail de teste disparado com sucesso!')
                ->body("Mensagem enviada para {$this->testRecipientEmail}.")
                ->success()
                ->send();

        } catch (\Throwable $e) {
            Notification::make()
                ->title('Erro ao disparar e-mail de teste')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
