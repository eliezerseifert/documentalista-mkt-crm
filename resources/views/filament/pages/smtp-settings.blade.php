<x-filament-panels::page>
    <div class="space-y-6">
        <form wire:submit.prevent="save" class="space-y-6">
            {{ $this->form }}

            <div class="flex items-center gap-3">
                <x-filament::button type="submit" icon="heroicon-o-check">
                    Salvar Configurações SMTP
                </x-filament::button>

                <x-filament::button color="info" wire:click="testConnection" icon="heroicon-o-arrow-path">
                    Testar Conexão Socket/Porta
                </x-filament::button>
            </div>
        </form>

        {{-- Card de Envio de E-mail de Teste --}}
        <div class="p-6 bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800 space-y-4">
            <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                <x-heroicon-o-paper-airplane class="w-5 h-5 text-emerald-500" />
                Disparo de E-mail de Teste
            </h3>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Dispare uma mensagem real para a sua caixa de entrada para verificar entregabilidade, DKIM/SPF e formatação visual.
            </p>

            <div class="max-w-md">
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                    E-mail do Destinatário:
                </label>
                <input type="email" wire:model="testRecipientEmail" placeholder="seu-email@dominio.com.br"
                       class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 text-sm p-2.5 focus:ring-primary-500 focus:border-primary-500" />
            </div>

            <div class="pt-2">
                <x-filament::button color="success" wire:click="sendTestEmail" icon="heroicon-o-envelope">
                    Enviar E-mail de Teste Agora
                </x-filament::button>
            </div>
        </div>

        {{-- Informações de Rastreamento & Conformidade LGPD --}}
        <div class="p-6 bg-blue-50/50 dark:bg-blue-950/20 rounded-xl border border-blue-200 dark:border-blue-800 space-y-3">
            <h4 class="font-bold text-blue-900 dark:text-blue-300 flex items-center gap-2 text-sm">
                <x-heroicon-o-shield-check class="w-5 h-5" />
                Rastreamento Automático e Conformidade LGPD
            </h4>
            <ul class="text-xs text-blue-900 dark:text-blue-300 space-y-1 list-disc list-inside">
                <li><strong>Pixel de Abertura:</strong> Todo e-mail enviado pelo motor de fluxos recebe uma tag oculta com pixel 1x1 transparente (<code>/t/open/{token}</code>) para contabilizar leituras e somar pontos de engajamento no Lead Score.</li>
                <li><strong>Links Rastreáveis:</strong> Botões de ação passam por rota de redirecionamento para registrar cliques e parâmetros UTM antes de direcionar para o checkout <code>checkout.v2.e-flips.com.br</code>.</li>
                <li><strong>Link de Descadastro (LGPD):</strong> Todo e-mail inclui link de opt-out em 1 clique (<code>/unsubscribe/{token}</code>). Ao clicar, o lead é marcado como <em>Descadastrado</em> e sai imediatamente de qualquer fluxo ativo.</li>
            </ul>
        </div>
    </div>
</x-filament-panels::page>
