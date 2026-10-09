<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TestSmtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $recipientEmail,
    ) {}

    public function build(): static
    {
        return $this->subject('✅ Teste de Conexão SMTP - Documentalista Leads')
            ->html("<h2>Configuração SMTP Operacional!</h2><p>Este é um e-mail de teste enviado com sucesso através do painel do <strong>Documentalista Leads CRM</strong>.</p><p>Data/Hora: " . now()->format('d/m/Y H:i:s') . "</p>");
    }
}
