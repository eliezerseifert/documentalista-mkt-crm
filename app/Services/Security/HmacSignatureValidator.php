<?php

namespace App\Services\Security;

class HmacSignatureValidator
{
    /**
     * Valida a assinatura HMAC-SHA256 de um payload.
     */
    public function isValid(?string $providedSignature, string $payload, ?string $secret): bool
    {
        if (empty($secret)) {
            // Se nenhum secret estiver configurado, aceita por padrão ou quando não exigido
            return true;
        }

        if (empty($providedSignature)) {
            return false;
        }

        // Se vier no formato "sha256=hash"
        if (str_starts_with($providedSignature, 'sha256=')) {
            $providedSignature = substr($providedSignature, 7);
        }

        $expectedSignature = hash_hmac('sha256', $payload, $secret);

        return hash_equals($expectedSignature, $providedSignature);
    }
}
