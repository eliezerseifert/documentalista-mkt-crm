<?php

namespace App\Enums;

enum LeadStatus: string
{
    case Novo = 'novo';
    case EmFluxo1 = 'em_fluxo_1';
    case EmFluxo2 = 'em_fluxo_2';
    case Engajado = 'engajado';
    case Assinou = 'assinou';
    case NaoAssinou = 'nao_assinou';
    case Descadastrado = 'descadastrado';

    public function label(): string
    {
        return match ($this) {
            self::Novo => 'Novo Lead',
            self::EmFluxo1 => 'Em Teste (Fluxo 1)',
            self::EmFluxo2 => 'Pós-Teste (Fluxo 2)',
            self::Engajado => 'Engajado',
            self::Assinou => 'Assinou 🎉',
            self::NaoAssinou => 'Não Assinou',
            self::Descadastrado => 'Descadastrado',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Novo => 'gray',
            self::EmFluxo1 => 'info',
            self::EmFluxo2 => 'warning',
            self::Engajado => 'purple',
            self::Assinou => 'success',
            self::NaoAssinou => 'danger',
            self::Descadastrado => 'secondary',
        };
    }
}
