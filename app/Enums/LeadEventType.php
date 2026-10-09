<?php

namespace App\Enums;

enum LeadEventType: string
{
    case Cadastro = 'cadastro';
    case MensagemEnfileirada = 'mensagem_enfileirada';
    case MensagemEnviada = 'mensagem_enviada';
    case MensagemEntregue = 'mensagem_entregue';
    case MensagemLida = 'mensagem_lida';
    case EmailAberto = 'email_aberto';
    case LinkClicado = 'link_clicado';
    case RespostaWhatsApp = 'resposta_whatsapp';
    case ChegouCheckout = 'chegou_checkout';
    case Assinou = 'assinou';
    case OptOut = 'opt_out';

    public function label(): string
    {
        return match ($this) {
            self::Cadastro => 'Cadastro Recebido',
            self::MensagemEnfileirada => 'Mensagem Enfileirada',
            self::MensagemEnviada => 'Mensagem Enviada',
            self::MensagemEntregue => 'Mensagem Entregue',
            self::MensagemLida => 'Mensagem Lida',
            self::EmailAberto => 'E-mail Aberto',
            self::LinkClicado => 'Link Clicado',
            self::RespostaWhatsApp => 'Resposta no WhatsApp',
            self::ChegouCheckout => 'Chegou ao Checkout',
            self::Assinou => 'Assinatura Concluída',
            self::OptOut => 'Solicitou Descadastro / Opt-out',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Cadastro => 'heroicon-o-user-plus',
            self::MensagemEnfileirada => 'heroicon-o-clock',
            self::MensagemEnviada => 'heroicon-o-paper-airplane',
            self::MensagemEntregue => 'heroicon-o-check',
            self::MensagemLida => 'heroicon-o-check-circle',
            self::EmailAberto => 'heroicon-o-envelope-open',
            self::LinkClicado => 'heroicon-o-cursor-arrow-rays',
            self::RespostaWhatsApp => 'heroicon-o-chat-bubble-bottom-center-text',
            self::ChegouCheckout => 'heroicon-o-shopping-cart',
            self::Assinou => 'heroicon-o-sparkles',
            self::OptOut => 'heroicon-o-no-symbol',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Cadastro => 'info',
            self::MensagemEnfileirada => 'gray',
            self::MensagemEnviada => 'primary',
            self::MensagemEntregue => 'info',
            self::MensagemLida => 'success',
            self::EmailAberto => 'warning',
            self::LinkClicado => 'purple',
            self::RespostaWhatsApp => 'success',
            self::ChegouCheckout => 'warning',
            self::Assinou => 'success',
            self::OptOut => 'danger',
        };
    }
}
