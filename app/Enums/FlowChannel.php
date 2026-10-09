<?php

namespace App\Enums;

enum FlowChannel: string
{
    case WhatsApp = 'whatsapp';
    case Email = 'email';
    case Ambos = 'both';

    public function label(): string
    {
        return match ($this) {
            self::WhatsApp => 'WhatsApp',
            self::Email => 'E-mail',
            self::Ambos => 'WhatsApp & E-mail',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::WhatsApp => 'heroicon-o-chat-bubble-left-ellipsis',
            self::Email => 'heroicon-o-envelope',
            self::Ambos => 'heroicon-o-arrows-right-left',
        };
    }
}
