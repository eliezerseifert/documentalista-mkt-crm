<?php

namespace App\Filament\Pages;

use App\Models\IntegrationSetting;
use App\Models\WebhookLog;
use App\Services\Lead\LeadCaptureService;
use Filament\Actions\Action;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class LeadCaptureSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-left-on-rectangle';
    protected static ?string $navigationGroup = 'Integrações';
    protected static ?string $navigationLabel = 'Formulário de Captação';
    protected static ?string $title = 'Integração: Formulário de Captação SaaS';
    protected static string $view = 'filament.pages.lead-capture-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $setting = IntegrationSetting::get('lead_capture', [
            'hmac_secret' => Str::random(32),
            'is_active' => true,
        ]);

        $this->form->fill($setting);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(2)->schema([
                    TextInput::make('hmac_secret')
                        ->label('Secret HMAC (Chave Secreta de Assinatura)')
                        ->helperText('Utilize esta chave para assinar as requisições com HMAC-SHA256 no header X-Signature-SHA256.')
                        ->required()
                        ->suffixAction(
                            \Filament\Forms\Components\Actions\Action::make('generateSecret')
                                ->icon('heroicon-o-arrow-path')
                                ->tooltip('Gerar nova chave segura')
                                ->action(function ($set) {
                                    $set('hmac_secret', Str::random(32));
                                })
                        ),
                    Placeholder::make('webhook_url')
                        ->label('Endpoint do Webhook (POST)')
                        ->content(new HtmlString('<code>' . url('/api/webhooks/leads') . '</code>')),
                ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();
        IntegrationSetting::set('lead_capture', $state);

        Notification::make()
            ->title('Configurações do formulário salvas com sucesso!')
            ->success()
            ->send();
    }

    public function sendTestLead(): void
    {
        $fakerNames = ['Carlos Alberto Mendes', 'Mariana Souza Dias', 'Rodrigo Fagundes', 'Fernanda Lima Rocha'];
        $name = $fakerNames[array_rand($fakerNames)];
        $randNumber = rand(1000, 9999);
        $email = 'lead.teste.' . $randNumber . '@exemplo.com.br';
        $phone = '+55119' . rand(8000, 9999) . rand(1000, 9999);

        try {
            $service = app(LeadCaptureService::class);
            $lead = $service->process([
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'saas_user_id' => 'USR-' . rand(10000, 99999),
                'utm_source' => 'teste_painel',
                'utm_medium' => 'filament_admin',
                'utm_campaign' => 'teste_integracao',
            ], request()->ip(), ['User-Agent' => 'Filament-Test-Dispatcher']);

            Notification::make()
                ->title('Lead de teste enviado com sucesso!')
                ->body("Lead criado: {$lead->name} ({$lead->email}) inscrito no Fluxo 1.")
                ->success()
                ->send();

        } catch (\Throwable $e) {
            Notification::make()
                ->title('Falha ao enviar lead de teste')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function getRecentLogsProperty()
    {
        return WebhookLog::where('origin', 'form_capture')
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get();
    }
}
