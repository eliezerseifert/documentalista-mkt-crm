<x-filament-panels::page>
    <div class="space-y-6">
        <form wire:submit.prevent="save" class="space-y-6">
            {{ $this->form }}

            <div class="flex items-center gap-3">
                <x-filament::button type="submit" icon="heroicon-o-check">
                    Salvar Credenciais
                </x-filament::button>

                <x-filament::button color="info" wire:click="testConnection" icon="heroicon-o-arrow-path">
                    Testar Conexão com a Meta
                </x-filament::button>
            </div>
        </form>

        {{-- Card de Envio de Mensagem de Teste --}}
        <div class="p-6 bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800 space-y-4">
            <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                <x-heroicon-o-paper-airplane class="w-5 h-5 text-emerald-500" />
                Disparo de Mensagem de Teste (WhatsApp)
            </h3>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Envie uma mensagem direta para validar a entrega em um número real com WhatsApp ativo. Lembre-se que para receber mensagens livres, o número de teste deve ter interagido com sua linha nas últimas 24h, ou deve ser um número de teste cadastrado no painel Meta.
            </p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                        Telefone com DDD (Ex: 11988887777 ou +5511988887777):
                    </label>
                    <input type="text" wire:model="testPhoneNumber" placeholder="11988887777"
                           class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 text-sm p-2.5 focus:ring-primary-500 focus:border-primary-500" />
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                        Texto da Mensagem:
                    </label>
                    <input type="text" wire:model="testMessage"
                           class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 text-sm p-2.5 focus:ring-primary-500 focus:border-primary-500" />
                </div>
            </div>

            <div class="pt-2">
                <x-filament::button color="success" wire:click="sendTestMessage" icon="heroicon-o-chat-bubble-left-ellipsis">
                    Disparar Mensagem de Teste
                </x-filament::button>
            </div>
        </div>

        {{-- Painel de Dicas da Janela de 24h & Templates --}}
        <div class="p-6 bg-amber-50/50 dark:bg-amber-950/20 rounded-xl border border-amber-200 dark:border-amber-800 space-y-3">
            <h4 class="font-bold text-amber-800 dark:text-amber-300 flex items-center gap-2 text-sm">
                <x-heroicon-o-information-circle class="w-5 h-5" />
                Regras da Janela de 24h & Message Templates da Meta
            </h4>
            <ul class="text-xs text-amber-900 dark:text-amber-400 space-y-1 list-disc list-inside">
                <li><strong>Janela Ativa (< 24h):</strong> Se o lead respondeu recentemente, enviamos texto livre, botões interativos e mídia sem custo de template de marketing.</li>
                <li><strong>Fora da Janela (> 24h):</strong> O sistema automaticamente utiliza o <em>Message Template</em> mapeado na etapa do fluxo (ex: <code>lead_trial_step_1</code>).</li>
                <li><strong>Opt-out obrigatório:</strong> Se o lead enviar qualquer variação de "SAIR" ou "PARAR", o sistema interrompe imediatamente os envios e registra o evento na timeline.</li>
            </ul>
        </div>
    </div>
</x-filament-panels::page>
