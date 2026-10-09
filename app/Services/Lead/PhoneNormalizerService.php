<?php

namespace App\Services\Lead;

class PhoneNormalizerService
{
    /**
     * Normaliza números de telefone brasileiros para o formato internacional E.164 (+55DD9XXXXXXXX).
     */
    public function normalize(string $phone): string
    {
        // Remove tudo que não for dígito numérico
        $digits = preg_replace('/\D/', '', $phone);

        // Se começar com 0 nacional, remove
        if (str_starts_with($digits, '0')) {
            $digits = substr($digits, 1);
        }

        // Se não tiver o DDI 55 do Brasil
        if (!str_starts_with($digits, '55')) {
            // Se tiver 10 ou 11 dígitos, é DDD + Número
            if (strlen($digits) === 10 || strlen($digits) === 11) {
                $digits = '55' . $digits;
            }
        }

        // Se tiver DDI 55 e 12 dígitos (55 + DDD de 2 + 8 dígitos de celular antigo), insere o 9º dígito
        if (str_starts_with($digits, '55') && strlen($digits) === 12) {
            $ddi = substr($digits, 0, 2);
            $ddd = substr($digits, 2, 2);
            $number = substr($digits, 4);

            // Celulares no Brasil iniciam geralmente entre 6 e 9
            $firstDigit = $number[0];
            if (in_array($firstDigit, ['6', '7', '8', '9'])) {
                $digits = $ddi . $ddd . '9' . $number;
            }
        }

        return '+' . $digits;
    }

    /**
     * Valida se o número possui comprimento compatível com E.164 brasileiro.
     */
    public function isValidBrazilianE164(string $e164): bool
    {
        return (bool) preg_match('/^\+55\d{10,11}$/', $e164);
    }
}
