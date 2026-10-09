<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Card de Configuração --}}
        <div class="p-6 bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800 space-y-4">
            <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                <x-heroicon-o-key class="w-5 h-5 text-primary-500" />
                Credenciais e Endpoint do Webhook
            </h3>
            
            <form wire:submit.prevent="save" class="space-y-4">
                {{ $this->form }}

                <div class="flex items-center gap-3 pt-2">
                    <x-filament::button type="submit" icon="heroicon-o-check">
                        Salvar Credenciais
                    </x-filament::button>

                    <x-filament::button color="success" wire:click="sendTestLead" icon="heroicon-o-paper-airplane">
                        Enviar Lead de Teste Agora
                    </x-filament::button>
                </div>
            </form>
        </div>

        {{-- Exemplo de Integração / Payload --}}
        <div class="p-6 bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800 space-y-4">
            <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                <x-heroicon-o-code-bracket-square class="w-5 h-5 text-info-500" />
                Instruções de Integração (Sistema Documentalista)
            </h3>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Configure o formulário de cadastro em <strong>https://app.documentalista.com.br/register</strong> para disparar um POST sempre que um novo usuário criar uma conta. Se o HMAC Secret estiver preenchido acima, envie o hash gerado no cabeçalho <code>X-Signature-SHA256</code>.
            </p>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <div>
                    <span class="text-xs font-semibold uppercase text-gray-500">Exemplo de Payload JSON (POST):</span>
                    <pre class="mt-1 p-3 bg-gray-950 text-emerald-400 rounded-lg text-xs overflow-x-auto font-mono">
{
  "name": "Dr. Marcelo Vieira",
  "email": "marcelo.vieira@advocacia.com.br",
  "phone": "(11) 98765-4321",
  "saas_user_id": "usr_948201",
  "registered_at": "{{ now()->toIso8601String() }}",
  "utm_source": "google_ads",
  "utm_medium": "cpc",
  "utm_campaign": "certidoes_empresariais",
  "meta": {
    "empresa": "Vieira Advogados Associados",
    "ramo": "Direito Imobiliário"
  }
}</pre>
                </div>

                <div>
                    <span class="text-xs font-semibold uppercase text-gray-500">Exemplo cURL com assinatura HMAC:</span>
                    <pre class="mt-1 p-3 bg-gray-950 text-cyan-400 rounded-lg text-xs overflow-x-auto font-mono">
curl -X POST "{{ url('/api/webhooks/leads') }}" \
  -H "Content-Type: application/json" \
  -H "X-Signature-SHA256: [HASH_HMAC_SHA256]" \
  -d '{
    "name": "Dr. Marcelo Vieira",
    "email": "marcelo.vieira@advocacia.com.br",
    "phone": "(11) 98765-4321",
    "saas_user_id": "usr_948201"
  }'</pre>
                </div>
            </div>
        </div>

        {{-- Log das Últimas Requisições Recebidas --}}
        <div class="p-6 bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800 space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                    <x-heroicon-o-table-cells class="w-5 h-5 text-warning-500" />
                    Últimas Requisições Recebidas (Webhook Logs)
                </h3>
                <span class="text-xs text-gray-500">Exibindo os 10 registros mais recentes</span>
            </div>

            <div class="overflow-x-auto border border-gray-200 dark:border-gray-800 rounded-lg">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs uppercase bg-gray-50 dark:bg-gray-800 text-gray-600 dark:text-gray-300">
                        <tr>
                            <th class="px-4 py-3">ID</th>
                            <th class="px-4 py-3">Data/Hora</th>
                            <th class="px-4 py-3">IP</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Lead / Email</th>
                            <th class="px-4 py-3">Erro</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @forelse($this->recentLogs as $log)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                <td class="px-4 py-3 font-mono text-xs">#{{ $log->id }}</td>
                                <td class="px-4 py-3 text-xs">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                                <td class="px-4 py-3 text-xs font-mono">{{ $log->ip_address ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    @if($log->response_status >= 200 && $log->response_status < 300)
                                        <span class="px-2 py-0.5 text-xs rounded-full bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-400 font-semibold">
                                            {{ $log->response_status }} OK
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 text-xs rounded-full bg-red-100 text-red-800 dark:bg-red-950 dark:text-red-400 font-semibold">
                                            {{ $log->response_status }} ERRO
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-xs">
                                    <strong>{{ $log->payload['name'] ?? '-' }}</strong><br>
                                    <span class="text-gray-500">{{ $log->payload['email'] ?? '-' }}</span>
                                </td>
                                <td class="px-4 py-3 text-xs text-red-600 dark:text-red-400 font-mono">
                                    {{ $log->error_message ?? '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-6 text-center text-gray-500 text-xs">
                                    Nenhuma requisição recebida ainda. Use o botão "Enviar Lead de Teste Agora" para simular o primeiro lead.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-filament-panels::page>
