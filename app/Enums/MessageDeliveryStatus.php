<?php

namespace App\Enums;

enum MessageDeliveryStatus: string
{
    case Enfileirada = 'enfileirada';
    case Enviada = 'enviada';
    case Entregue = 'entregue';
    case Lida = 'lida';
    case Falhou = 'falhou';

    public function label(): string
    {
        return match ($this) {
            self::Enfileirada => 'Enfileirada',
            self::Enviada => 'Enviada',
            self::Entregue => 'Entregue',
            self::Lida => 'Lida',
            self::Falhou => 'Falhou',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Enfileirada => 'gray',
            self::Enviada => 'info',
            self::Entregue => 'primary',
            self::Lida => 'success',
            self::Falhou => 'danger',
        };
    }
}
