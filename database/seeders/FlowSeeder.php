<?php

namespace Database\Seeders;

use App\Enums\FlowChannel;
use App\Models\Flow;
use App\Models\FlowStep;
use App\Models\StepMessage;
use Illuminate\Database\Seeder;

class FlowSeeder extends Seeder
{
    public function run(): void
    {
        // -------------------------------------------------------------
        // FLUXO 1: 15 DIAS DE TESTE GRÁTIS
        // -------------------------------------------------------------
        $flow1 = Flow::firstOrCreate(
            ['slug' => 'periodo-teste-15-dias'],
            [
                'name' => 'Fluxo 1: Período de Teste Grátis (15 dias)',
                'description' => 'Nutrição diária durante o período de 15 dias de teste grátis do SaaS Documentalista.',
                'is_active' => true,
                'total_days' => 15,
            ]
        );

        $flow1Steps = [
            1 => [
                'day_offset' => 0,
                'time' => '09:00:00',
                'channel' => FlowChannel::Ambos,
                'subject' => '🎉 Seja bem-vindo ao Documentalista! Seu teste grátis começou',
                'wpp_text' => "Olá, {{primeiro_nome}}! Parabéns por iniciar seu teste de 15 dias no *Documentalista*.\n\nVocê já tem acesso a emissão de certidões, consultas automáticas e dossiês completos.\n\nQuer agilizar sua primeira busca agora mesmo?",
                'email_text' => "<p>Olá, <strong>{{primeiro_nome}}</strong>!</p><p>Seja muito bem-vindo ao <strong>Documentalista</strong>. Seu período de 15 dias de teste gratuito já está ativo!</p><p>Com a nossa plataforma, você elimina horas em filas de cartórios e órgãos públicos, emitindo certidões e gerando relatórios em minutos.</p><p>Acesse o painel e faça sua primeira consulta agora mesmo.</p>",
                'button_label' => 'Acessar o Sistema',
                'button_url' => 'https://app.documentalista.com.br/login',
            ],
            2 => [
                'day_offset' => 1,
                'time' => '10:00:00',
                'channel' => FlowChannel::Ambos,
                'subject' => '⚡ Dica #1: Como emitir sua primeira certidão em 3 cliques',
                'wpp_text' => "Oi, {{primeiro_nome}}! Sabia que você pode pesquisar certidões estaduais e federais em menos de 1 minuto no Documentalista?\n\nVeja este tutorial rápido e poupe horas da sua rotina.",
                'email_text' => "<p>Olá, {{primeiro_nome}}!</p><p>Sabemos que o seu tempo é precioso. Por isso, preparamos um passo a passo prático para você emitir qualquer certidão em apenas 3 cliques dentro do Documentalista.</p><p>Economize tempo da sua equipe jurídica ou contábil agora mesmo.</p>",
                'button_label' => 'Ver Como Funciona',
                'button_url' => 'https://app.documentalista.com.br/ajuda/certidoes',
            ],
            3 => [
                'day_offset' => 2,
                'time' => '09:30:00',
                'channel' => FlowChannel::Ambos,
                'subject' => '🔍 Monitoramento automático de processos e certidões',
                'wpp_text' => "Tudo bem, {{primeiro_nome}}? No Documentalista você não precisa consultar tudo manualmente todo dia. Nosso robô faz isso por você!\n\nVocê tem {{dias_restantes_trial}} dias de teste grátis restantes.",
                'email_text' => "<p>Olá {{primeiro_nome}},</p><p>Você sabia que o Documentalista pode monitorar automaticamente alterações de status e emitir alertas sempre que houver novidades?</p><p>Aproveite os seus {{dias_restantes_trial}} dias restantes de teste grátis para ativar seus primeiros alertas.</p>",
                'button_label' => 'Ativar Monitoramento',
                'button_url' => 'https://app.documentalista.com.br/alertas',
            ],
            4 => [
                'day_offset' => 3,
                'time' => '10:00:00',
                'channel' => FlowChannel::Ambos,
                'subject' => '📄 Modelos de Dossiês prontos para seus clientes',
                'wpp_text' => "{{primeiro_nome}}, impressione seus clientes com relatórios profissionais com a identidade do seu escritório. Baixe em PDF no Documentalista!",
                'email_text' => "<p>Olá {{primeiro_nome}}!</p><p>Gere dossiês completos de compliance, due diligence e pesquisa patrimonial com layout profissional em PDF para apresentar aos seus clientes.</p><p>Acesse o painel e teste os modelos disponíveis.</p>",
                'button_label' => 'Gerar Meu Dossiê',
                'button_url' => 'https://app.documentalista.com.br/relatorios',
            ],
            5 => [
                'day_offset' => 4,
                'time' => '09:00:00',
                'channel' => FlowChannel::Ambos,
                'subject' => '⏱️ Quanto tempo seu escritório perde com burocracia?',
                'wpp_text' => "Oi, {{primeiro_nome}}! Escritórios que usam o Documentalista reduzem até 78% do tempo gasto em emissão de certidões.\n\nRestam {{dias_restantes_trial}} dias no seu teste grátis. Aproveite!",
                'email_text' => "<p>Olá {{primeiro_nome}},</p><p>Em média, um profissional perde de 4 a 6 horas por semana acessando múltiplos portais públicos. Com o Documentalista, tudo fica centralizado em uma única tela.</p><p>Não deixe suas consultas acumularem!</p>",
                'button_label' => 'Centralizar Consultas',
                'button_url' => 'https://app.documentalista.com.br',
            ],
            6 => [
                'day_offset' => 5,
                'time' => '10:30:00',
                'channel' => FlowChannel::Ambos,
                'subject' => '👥 Convide sua equipe para colaborar',
                'wpp_text' => "{{primeiro_nome}}, você pode adicionar membros da sua equipe para consultar e emitir certidões juntos no Documentalista. É muito fácil!",
                'email_text' => "<p>Olá {{primeiro_nome}},</p><p>Trabalhe em equipe! No Documentalista você pode conceder acessos com permissões personalizadas para assistentes e analistas.</p><p>Convide seus colegas e multiplique a produtividade.</p>",
                'button_label' => 'Adicionar Colaboradores',
                'button_url' => 'https://app.documentalista.com.br/equipe',
            ],
            7 => [
                'day_offset' => 6,
                'time' => '09:00:00',
                'channel' => FlowChannel::Ambos,
                'subject' => '🛡️ Segurança jurídica: documentos com validade garantida',
                'wpp_text' => "Olá, {{primeiro_nome}}! Todas as certidões emitidas pelo Documentalista são autênticas e possuem código verificador oficial. Segurança total para suas operações.",
                'email_text' => "<p>Olá {{primeiro_nome}},</p><p>Segurança jurídica é prioridade máxima. Nossos sistemas integram diretamente com as bases oficiais com verificação de autenticidade e carimbo de data/hora.</p>",
                'button_label' => 'Consultar Segurança',
                'button_url' => 'https://app.documentalista.com.br',
            ],
            8 => [
                'day_offset' => 7,
                'time' => '10:00:00',
                'channel' => FlowChannel::Ambos,
                'subject' => '⏳ Metade do seu teste já passou! Como tem sido sua experiência?',
                'wpp_text' => "Oi, {{primeiro_nome}}! Já se passaram 7 dias do seu teste. Restam {{dias_restantes_trial}} dias.\n\nPrecisa de ajuda para configurar alguma consulta específica? Responda aqui!",
                'email_text' => "<p>Olá {{primeiro_nome}},</p><p>Você já está na metade do seu período de avaliação! Queremos ter certeza de que você está aproveitando ao máximo todas as ferramentas.</p><p>Se tiver qualquer dúvida, nosso suporte especializado está pronto para ajudar.</p>",
                'button_label' => 'Falar com Especialista',
                'button_url' => 'https://app.documentalista.com.br/suporte',
            ],
            9 => [
                'day_offset' => 8,
                'time' => '09:15:00',
                'channel' => FlowChannel::Ambos,
                'subject' => '📊 Histórico unificado: nunca mais perca um comprovante',
                'wpp_text' => "{{primeiro_nome}}, tudo que você pesquisa fica salvo na nuvem com backup automático. Encontre certidões antigas em segundos no Documentalista.",
                'email_text' => "<p>Olá {{primeiro_nome}},</p><p>Com o histórico unificado do Documentalista, você nunca mais precisará procurar arquivos em pastas espalhadas no computador. Pesquise por CPF/CNPJ e recupere documentos emitidos a qualquer momento.</p>",
                'button_label' => 'Ver Meu Histórico',
                'button_url' => 'https://app.documentalista.com.br/historico',
            ],
            10 => [
                'day_offset' => 9,
                'time' => '10:00:00',
                'channel' => FlowChannel::Ambos,
                'subject' => '💡 Casos reais: veja quem já economiza mais de R$ 3.000/mês',
                'wpp_text' => "Oi, {{primeiro_nome}}! Veja como outros escritórios e despachantes aumentaram a rentabilidade e agilidade com o Documentalista.",
                'email_text' => "<p>Olá {{primeiro_nome}},</p><p>Mais de 500 escritórios e empresas confiam no Documentalista para otimizar suas rotinas e reduzir custos operacionais.</p><p>Garanta a continuidade do seu serviço sem interrupções.</p>",
                'button_label' => 'Conhecer os Planos',
                'button_url' => '{{link_checkout}}',
            ],
            11 => [
                'day_offset' => 10,
                'time' => '09:00:00',
                'channel' => FlowChannel::Ambos,
                'subject' => '⚠️ Atenção: restam menos de 5 dias do seu teste gratuito',
                'wpp_text' => "Aviso importante, {{primeiro_nome}}: seu teste grátis encerra em {{dias_restantes_trial}} dias.\n\nGaranta sua assinatura agora para não perder o acesso às suas consultas e histórico!",
                'email_text' => "<p>Olá {{primeiro_nome}},</p><p>O seu teste grátis está chegando ao fim. Restam apenas <strong>{{dias_restantes_trial}} dias</strong>.</p><p>Assine agora mesmo para garantir que suas automações e alertas continuem ativos sem pausa.</p>",
                'button_label' => 'Garantir Minha Assinatura',
                'button_url' => '{{link_checkout}}',
            ],
            12 => [
                'day_offset' => 11,
                'time' => '10:30:00',
                'channel' => FlowChannel::Ambos,
                'subject' => '🚀 Dê um salto de produtividade sem burocracia',
                'wpp_text' => "{{primeiro_nome}}, mantenha seus clientes satisfeitos com entregas rápidas de certidões. Assine o Documentalista e tenha tranquilidade no dia a dia.",
                'email_text' => "<p>Olá {{primeiro_nome}},</p><p>Não deixe para a última hora. Assinar o Documentalista leva menos de 2 minutos e garante acesso contínuo aos melhores recursos de certidões do mercado.</p>",
                'button_label' => 'Ativar Assinatura Imediata',
                'button_url' => '{{link_checkout}}',
            ],
            13 => [
                'day_offset' => 12,
                'time' => '09:00:00',
                'channel' => FlowChannel::Ambos,
                'subject' => '⏳ Restam 48 horas! Seu teste do Documentalista vai expirar',
                'wpp_text' => "{{primeiro_nome}}, restam apenas 2 dias de teste gratuito!\n\nClique no link abaixo para assinar agora e continuar usando sem interrupções.",
                'email_text' => "<p>Olá <strong>{{primeiro_nome}}</strong>,</p><p>Faltam apenas 48 horas para o término do seu teste gratuito no Documentalista.</p><p>Após esse prazo, seu acesso às consultas e monitoramentos será bloqueado. Evite transtornos concluindo sua assinatura hoje.</p>",
                'button_label' => 'Renovar com Continuidade',
                'button_url' => '{{link_checkout}}',
            ],
            14 => [
                'day_offset' => 13,
                'time' => '09:00:00',
                'channel' => FlowChannel::Ambos,
                'subject' => '🚨 ÚLTIMO DIA de teste gratuito do Documentalista!',
                'wpp_text' => "URGENTE, {{primeiro_nome}}! Seu teste grátis termina hoje!\n\nEvite o bloqueio do seu painel assinando agora pelo checkout oficial.",
                'email_text' => "<p>Olá <strong>{{primeiro_nome}}</strong>,</p><p>Hoje é o último dia do seu teste de 15 dias no <strong>Documentalista</strong>.</p><p>Para manter suas configurações, histórico e consultas salvas, finalize sua assinatura agora mesmo.</p>",
                'button_label' => 'Assinar Agora Antes do Bloqueio',
                'button_url' => '{{link_checkout}}',
            ],
            15 => [
                'day_offset' => 14,
                'time' => '10:00:00',
                'channel' => FlowChannel::Ambos,
                'subject' => '🔒 Seu teste expirou. Não perca seu histórico no Documentalista',
                'wpp_text' => "{{primeiro_nome}}, seu período de 15 dias de teste encerrou.\n\nPara desbloquear seu acesso e continuar emitindo certidões, clique abaixo e assine:",
                'email_text' => "<p>Olá {{primeiro_nome}},</p><p>O período de teste do Documentalista chegou ao fim. Seu acesso às novas emissões foi pausado.</p><p>Basta reativar seu plano no link abaixo para voltar a usar imediatamente com todos os seus dados preservados.</p>",
                'button_label' => 'Reativar Minha Conta Agora',
                'button_url' => '{{link_checkout}}',
            ],
        ];

        foreach ($flow1Steps as $stepNumber => $stepData) {
            $step = FlowStep::firstOrCreate(
                [
                    'flow_id' => $flow1->id,
                    'step_number' => $stepNumber,
                ],
                [
                    'day_offset' => $stepData['day_offset'],
                    'send_time' => $stepData['time'],
                    'channel' => $stepData['channel'],
                    'is_active' => true,
                    'is_promotion' => false,
                ]
            );

            // Mensagem WhatsApp
            StepMessage::updateOrCreate(
                [
                    'flow_step_id' => $step->id,
                    'channel' => 'whatsapp',
                ],
                [
                    'body_text' => $stepData['wpp_text'],
                    'button_label' => $stepData['button_label'],
                    'button_url' => $stepData['button_url'],
                    'whatsapp_template_name' => 'lead_trial_step_' . $stepNumber,
                ]
            );

            // Mensagem E-mail
            StepMessage::updateOrCreate(
                [
                    'flow_step_id' => $step->id,
                    'channel' => 'email',
                ],
                [
                    'subject' => $stepData['subject'],
                    'body_text' => $stepData['email_text'],
                    'button_label' => $stepData['button_label'],
                    'button_url' => $stepData['button_url'],
                ]
            );
        }

        // -------------------------------------------------------------
        // FLUXO 2: PÓS-TESTE - CONVERSÃO & RETENÇÃO (DIAS 16 A 30)
        // -------------------------------------------------------------
        $flow2 = Flow::firstOrCreate(
            ['slug' => 'pos-teste-conversao-15-dias'],
            [
                'name' => 'Fluxo 2: Pós-teste: Conversão (15 dias)',
                'description' => 'Cadência de 15 dias para leads que não assinaram ao fim do Fluxo 1, incluindo promoção imperdível no dia 20.',
                'is_active' => true,
                'total_days' => 15,
            ]
        );

        $flow2Steps = [
            1 => [ // Dia 16 do ciclo
                'day_offset' => 15,
                'time' => '10:00:00',
                'channel' => FlowChannel::Ambos,
                'is_promo' => false,
                'coupon' => null,
                'subject' => 'Sentimos sua falta no Documentalista, {{primeiro_nome}}',
                'wpp_text' => "Olá, {{primeiro_nome}}! Notamos que seu teste terminou e você ainda não ativou sua assinatura.\n\nHouve alguma dificuldade técnica ou dúvida sobre os planos? Estamos aqui para te ajudar!",
                'email_text' => "<p>Olá {{primeiro_nome}},</p><p>Gostaríamos de saber como foi sua experiência com o Documentalista. Seus processos ficaram mais fáceis?</p><p>Caso tenha alguma dúvida sobre os planos ou precise de uma proposta personalizada, conte conosco.</p>",
                'button_label' => 'Ver Opções de Assinatura',
                'button_url' => '{{link_checkout}}',
            ],
            2 => [ // Dia 17 do ciclo
                'day_offset' => 16,
                'time' => '09:30:00',
                'channel' => FlowChannel::Ambos,
                'is_promo' => false,
                'coupon' => null,
                'subject' => 'Não deixe seus clientes esperando por certidões',
                'wpp_text' => "{{primeiro_nome}}, a agilidade nas certidões faz toda a diferença para fechar contratos com seus clientes. Reative sua conta agora!",
                'email_text' => "<p>Olá {{primeiro_nome}},</p><p>Não volte à rotina lenta dos sites governamentais instáveis. Com o Documentalista, você ganha rapidez e credibilidade.</p>",
                'button_label' => 'Voltar para o Documentalista',
                'button_url' => '{{link_checkout}}',
            ],
            3 => [ // Dia 18 do ciclo
                'day_offset' => 17,
                'time' => '10:00:00',
                'channel' => FlowChannel::Ambos,
                'is_promo' => false,
                'coupon' => null,
                'subject' => 'Comparativo: Documentalista vs. Consultas Manuais',
                'wpp_text' => "Oi, {{primeiro_nome}}! Sabia que fazer buscas manuais custa 3x mais caro em horas trabalhadas do que uma assinatura do Documentalista?",
                'email_text' => "<p>Olá {{primeiro_nome}},</p><p>Coloque na ponta do lápis: quanto vale 1 hora do seu dia? O Documentalista se paga logo nas primeiras certidões emitidas.</p>",
                'button_label' => 'Assinar com Melhor Custo-Benefício',
                'button_url' => '{{link_checkout}}',
            ],
            4 => [ // Dia 19 do ciclo
                'day_offset' => 18,
                'time' => '09:00:00',
                'channel' => FlowChannel::Ambos,
                'is_promo' => false,
                'coupon' => null,
                'subject' => 'Temos uma surpresa especial para você amanhã...',
                'wpp_text' => "{{primeiro_nome}}, prepare-se! Amanhã liberamos uma condição imperdível e exclusiva para você ter o Documentalista no seu negócio. Fique atento!",
                'email_text' => "<p>Olá {{primeiro_nome}},</p><p>Nossa equipe aprovou uma oferta exclusiva e por tempo limitado para reativar seu acesso. Amanhã enviaremos os detalhes!</p>",
                'button_label' => 'Ficar Atento à Oferta',
                'button_url' => '{{link_checkout}}',
            ],
            5 => [ // Dia 20 do ciclo: PROMOÇÃO IMPERDÍVEL (50% OFF)
                'day_offset' => 19,
                'time' => '09:00:00',
                'channel' => FlowChannel::Ambos,
                'is_promo' => true,
                'coupon' => 'DOC50OFF',
                'subject' => '🎁 50% DE DESCONTO EXCLUSIVO no Documentalista (Válido por 48h)!',
                'wpp_text' => "🔥 MEGA OPORTUNIDADE, {{primeiro_nome}}!\n\nLiberamos 50% de DESCONTO no seu primeiro ciclo com o cupom *DOC50OFF*.\n\nEssa oferta é válida apenas por 48 horas! Aproveite agora:",
                'email_text' => "<p>Olá <strong>{{primeiro_nome}}</strong>,</p><p>Queremos muito que você tenha toda a potência do <strong>Documentalista</strong> no seu dia a dia.</p><div style='background:#f0fdf4; border:1px dashed #16a34a; padding:15px; border-radius:8px; text-align:center;'><h3>CUPOM EXCLUSIVO: <strong>DOC50OFF</strong></h3><p>50% de desconto no seu plano. Válido por 48 horas!</p></div><p>Clique no botão abaixo para aplicar o cupom diretamente no checkout seguro:</p>",
                'button_label' => 'Quero 50% de Desconto Agora',
                'button_url' => 'https://checkout.v2.e-flips.com.br?coupon=DOC50OFF',
            ],
            6 => [ // Dia 21 do ciclo
                'day_offset' => 20,
                'time' => '10:00:00',
                'channel' => FlowChannel::Ambos,
                'is_promo' => true,
                'coupon' => 'DOC50OFF',
                'subject' => '⏰ Seu cupom de 50% expira em menos de 24 horas!',
                'wpp_text' => "{{primeiro_nome}}, lembrete rápido: o cupom *DOC50OFF* de 50% OFF expira amanhã. Não deixe passar essa oportunidade de economizar!",
                'email_text' => "<p>Olá {{primeiro_nome}},</p><p>Restam poucas horas para o encerramento do cupom <strong>DOC50OFF</strong> com 50% de desconto.</p><p>Garanta sua assinatura agora antes que o valor retorne à tabela cheia.</p>",
                'button_label' => 'Usar Meu Cupom Agora',
                'button_url' => 'https://checkout.v2.e-flips.com.br?coupon=DOC50OFF',
            ],
            7 => [ // Dia 22 do ciclo
                'day_offset' => 21,
                'time' => '18:00:00',
                'channel' => FlowChannel::Ambos,
                'is_promo' => true,
                'coupon' => 'DOC50OFF',
                'subject' => '🚨 ÚLTIMAS HORAS da condição de 50% OFF no Documentalista',
                'wpp_text' => "ÚLTIMA CHAMADA, {{primeiro_nome}}! O cupom *DOC50OFF* expira hoje à meia-noite. Clique e ative com 50% de desconto:",
                'email_text' => "<p>Olá {{primeiro_nome}},</p><p>Esta é a última chance de ativar o Documentalista com 50% de desconto utilizando o cupom <strong>DOC50OFF</strong>.</p>",
                'button_label' => 'Ativar Agora Antes que Expire',
                'button_url' => 'https://checkout.v2.e-flips.com.br?coupon=DOC50OFF',
            ],
            8 => [ // Dia 23 do ciclo
                'day_offset' => 22,
                'time' => '10:00:00',
                'channel' => FlowChannel::Ambos,
                'is_promo' => false,
                'coupon' => null,
                'subject' => 'Como podemos te ajudar a tomar a melhor decisão?',
                'wpp_text' => "Oi, {{primeiro_nome}}! Vimos que você não conseguiu aproveitar o cupom. Gostaria de conversar com nosso consultor para tirar dúvidas?",
                'email_text' => "<p>Olá {{primeiro_nome}},</p><p>Entendemos que cada negócio tem suas particularidades. Se quiser entender melhor como podemos nos adaptar à sua demanda, nos responda aqui.</p>",
                'button_label' => 'Conversar com Consultor',
                'button_url' => 'https://app.documentalista.com.br/suporte',
            ],
            9 => [ // Dia 24 do ciclo
                'day_offset' => 23,
                'time' => '09:00:00',
                'channel' => FlowChannel::Ambos,
                'is_promo' => false,
                'coupon' => null,
                'subject' => 'Acesso rápido a mais de 30 tipos de certidões',
                'wpp_text' => "{{primeiro_nome}}, você tem acesso a certidões da Receita, Justiça Federal, FGTS, CNDs estaduais e muito mais com um clique. Volte para o Documentalista!",
                'email_text' => "<p>Olá {{primeiro_nome}},</p><p>Centralize todas as certidões em um único lugar. O Documentalista simplifica seu trabalho e evita erros manuais.</p>",
                'button_label' => 'Ver Todas as Certidões',
                'button_url' => '{{link_checkout}}',
            ],
            10 => [ // Dia 25 do ciclo
                'day_offset' => 24,
                'time' => '10:00:00',
                'channel' => FlowChannel::Ambos,
                'is_promo' => false,
                'coupon' => null,
                'subject' => 'Seus concorrentes já estão automatizando certidões',
                'wpp_text' => "{{primeiro_nome}}, enquanto você gasta tempo com filas online, escritórios modernos entregam tudo em minutos. Fique à frente com o Documentalista!",
                'email_text' => "<p>Olá {{primeiro_nome}},</p><p>Agilidade no atendimento é o principal diferencial competitivo para reter clientes. Recupere seu acesso ao Documentalista hoje.</p>",
                'button_label' => 'Garantir Minha Vantagem',
                'button_url' => '{{link_checkout}}',
            ],
            11 => [ // Dia 26 do ciclo
                'day_offset' => 25,
                'time' => '09:30:00',
                'channel' => FlowChannel::Ambos,
                'is_promo' => false,
                'coupon' => null,
                'subject' => 'Segurança de dados e conformidade total com LGPD',
                'wpp_text' => "Oi, {{primeiro_nome}}! O Documentalista é 100% aderente à LGPD e criptografa todos os dados e certidões dos seus clientes. Proteção garantida.",
                'email_text' => "<p>Olá {{primeiro_nome}},</p><p>Trabalhe com total conformidade e tranquilidade. Nossa infraestrutura garante sigilo e proteção completa de dados.</p>",
                'button_label' => 'Assinar com Segurança',
                'button_url' => '{{link_checkout}}',
            ],
            12 => [ // Dia 27 do ciclo
                'day_offset' => 26,
                'time' => '10:00:00',
                'channel' => FlowChannel::Ambos,
                'is_promo' => false,
                'coupon' => null,
                'subject' => 'Estamos quase encerrando seu ciclo de acompanhamento',
                'wpp_text' => "{{primeiro_nome}}, seu ciclo de teste e acompanhamento está chegando ao fim. Não perca a oportunidade de ter a melhor ferramenta ao seu lado.",
                'email_text' => "<p>Olá {{primeiro_nome}},</p><p>Faltam poucos dias para encerrarmos as comunicações com ofertas para sua conta. Se ainda deseja assinar, aproveite hoje.</p>",
                'button_label' => 'Ativar Plano Agora',
                'button_url' => '{{link_checkout}}',
            ],
            13 => [ // Dia 28 do ciclo
                'day_offset' => 27,
                'time' => '09:00:00',
                'channel' => FlowChannel::Ambos,
                'is_promo' => false,
                'coupon' => null,
                'subject' => 'Última oportunidade: parcelamento facilitado no cartão',
                'wpp_text' => "{{primeiro_nome}}, você pode assinar o plano anual com parcelamento facilitado e economizar ainda mais. Confira no checkout seguro:",
                'email_text' => "<p>Olá {{primeiro_nome}},</p><p>Temos planos anuais com descontos agressivos e parcelamento sem complicação. Escolha o ideal para o seu volume de consultas.</p>",
                'button_label' => 'Ver Planos Anuais',
                'button_url' => '{{link_checkout}}',
            ],
            14 => [ // Dia 29 do ciclo
                'day_offset' => 28,
                'time' => '10:00:00',
                'channel' => FlowChannel::Ambos,
                'is_promo' => false,
                'coupon' => null,
                'subject' => 'Amanhã encerramos seu cadastro no ciclo promocional',
                'wpp_text' => "{{primeiro_nome}}, amanhã encerramos seu ciclo de 30 dias. Para não perder seu cadastro e benefícios, clique abaixo e conclua sua assinatura:",
                'email_text' => "<p>Olá {{primeiro_nome}},</p><p>Este é um dos últimos contatos do seu ciclo de 30 dias. Esperamos muito ter você como assinante oficial do Documentalista.</p>",
                'button_label' => 'Concluir Minha Assinatura',
                'button_url' => '{{link_checkout}}',
            ],
            15 => [ // Dia 30 do ciclo: ÚLTIMA TENTATIVA ANTES DE MARCAR COMO 'NÃO ASSINOU'
                'day_offset' => 29,
                'time' => '09:00:00',
                'channel' => FlowChannel::Ambos,
                'is_promo' => false,
                'coupon' => null,
                'subject' => 'Despedida: obrigado por testar o Documentalista',
                'wpp_text' => "{{primeiro_nome}}, hoje encerramos nosso ciclo de acompanhamento de 30 dias. Obrigado pelo seu tempo! Se decidir voltar a qualquer momento, o checkout continuará aberto para você.",
                'email_text' => "<p>Olá {{primeiro_nome}},</p><p>Hoje encerramos nosso ciclo de mensagens. Esperamos que o teste de 15 dias tenha sido útil para você conhecer a agilidade do Documentalista.</p><p>Seus dados de histórico serão arquivados. Se decidir reativar sua conta no futuro, basta acessar o link abaixo.</p><p>Desejamos muito sucesso ao seu negócio!</p>",
                'button_label' => 'Reativar Conta no Futuro',
                'button_url' => '{{link_checkout}}',
            ],
        ];

        foreach ($flow2Steps as $stepNumber => $stepData) {
            $step = FlowStep::firstOrCreate(
                [
                    'flow_id' => $flow2->id,
                    'step_number' => $stepNumber,
                ],
                [
                    'day_offset' => $stepData['day_offset'],
                    'send_time' => $stepData['time'],
                    'channel' => $stepData['channel'],
                    'is_active' => true,
                    'is_promotion' => $stepData['is_promo'],
                    'promo_coupon_code' => $stepData['coupon'],
                    'promo_expires_in_days' => $stepData['is_promo'] ? 2 : null,
                ]
            );

            // Mensagem WhatsApp
            StepMessage::updateOrCreate(
                [
                    'flow_step_id' => $step->id,
                    'channel' => 'whatsapp',
                ],
                [
                    'body_text' => $stepData['wpp_text'],
                    'button_label' => $stepData['button_label'],
                    'button_url' => $stepData['button_url'],
                    'whatsapp_template_name' => 'lead_post_trial_step_' . $stepNumber,
                ]
            );

            // Mensagem E-mail
            StepMessage::updateOrCreate(
                [
                    'flow_step_id' => $step->id,
                    'channel' => 'email',
                ],
                [
                    'subject' => $stepData['subject'],
                    'body_text' => $stepData['email_text'],
                    'button_label' => $stepData['button_label'],
                    'button_url' => $stepData['button_url'],
                ]
            );
        }
    }
}
