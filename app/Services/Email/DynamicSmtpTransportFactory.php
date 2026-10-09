<?php

namespace App\Services\Email;

use App\Models\IntegrationSetting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Bridge\Smtp\Transport\SmtpTransportFactory;
use Symfony\Component\Mailer\Transport\Dsn;
use Throwable;

class DynamicSmtpTransportFactory
{
    /**
     * Aplica as configurações do banco na configuração em tempo de execução do Laravel.
     */
    public function configureMailer(): bool
    {
        $settings = IntegrationSetting::get('smtp', []);

        if (empty($settings['host'])) {
            return false;
        }

        $scheme = match (strtolower($settings['encryption'] ?? 'tls')) {
            'ssl' => 'smtps',
            default => 'smtp',
        };

        Config::set('mail.mailers.dynamic_smtp', [
            'transport' => 'smtp',
            'host' => $settings['host'],
            'port' => (int) ($settings['port'] ?? 587),
            'encryption' => $settings['encryption'] ?? 'tls',
            'username' => $settings['username'] ?? null,
            'password' => $settings['password'] ?? null,
            'timeout' => 15,
            'local_domain' => env('MAIL_EHLO_DOMAIN'),
        ]);

        Config::set('mail.from', [
            'address' => $settings['from_email'] ?? 'atendimento@documentalista.com.br',
            'name' => $settings['from_name'] ?? 'Documentalista',
        ]);

        return true;
    }

    /**
     * Testa o handshake e autenticação com o servidor SMTP.
     */
    public function testConnection(): array
    {
        $settings = IntegrationSetting::get('smtp', []);

        if (empty($settings['host']) || empty($settings['port'])) {
            return [
                'success' => false,
                'message' => 'Servidor SMTP não configurado. Preencha os campos e salve.',
            ];
        }

        $host = $settings['host'];
        $port = (int) $settings['port'];
        $timeout = 5;

        // Teste inicial de socket
        $connection = @fsockopen($host, $port, $errno, $errstr, $timeout);
        if (!$connection) {
            return [
                'success' => false,
                'message' => "Não foi possível conectar ao host {$host}:{$port}. Erro ({$errno}): {$errstr}",
            ];
        }

        fclose($connection);

        return [
            'success' => true,
            'message' => "Conexão de socket com {$host}:{$port} estabelecida com sucesso!",
        ];
    }
}
