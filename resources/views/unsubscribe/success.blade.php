<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Descadastro Confirmado - Documentalista</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 text-slate-800 flex items-center justify-center min-h-screen p-4">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-xl p-8 text-center border border-slate-100">
        <div class="w-16 h-16 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
        </div>
        <h1 class="text-2xl font-bold text-slate-900 mb-2">Descadastro Concluído</h1>
        <p class="text-slate-600 mb-6 text-sm">
            O endereço <strong>{{ $lead->email }}</strong> foi removido com sucesso de todas as nossas comunicações automáticas.
        </p>
        <div class="p-4 bg-slate-50 rounded-xl text-xs text-slate-500 mb-6">
            Em conformidade com a LGPD (Lei Geral de Proteção de Dados), seus dados foram atualizados e nenhuma mensagem promocional adicional será enviada.
        </div>
        <a href="https://app.documentalista.com.br" class="inline-block px-5 py-2.5 bg-slate-900 text-white font-medium text-sm rounded-lg hover:bg-slate-800 transition">
            Ir para a Página Inicial
        </a>
    </div>
</body>
</html>
