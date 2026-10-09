# Documentalista Leads CRM & Automação de Marketing

Sistema completo de **Automação de Marketing e CRM** desenvolvido especificamente para captar leads vindos do formulário de cadastro do SaaS **Documentalista** ([app.documentalista.com.br/register](https://app.documentalista.com.br/register)) e convertê-los em assinantes no checkout oficial ([checkout.v2.e-flips.com.br](https://checkout.v2.e-flips.com.br)).

---

## 🚀 Stack Tecnológica

- **Backend:** PHP 8.3+ / PHP 8.5, Laravel 11/12
- **Painel Administrativo:** Filament PHP v3
- **Banco de Dados:** MySQL / PostgreSQL (suporta SQLite para testes)
- **Filas & Resiliência:** Redis + Laravel Queues (com suporte a Horizon e backoff exponencial)
- **Agendamento:** Laravel Scheduler (execução a cada minuto no fuso `America/Sao_Paulo`)
- **Segurança:** Criptografia em repouso (casts `encrypted`), validação HMAC SHA256 em webhooks, conformidade LGPD com opt-out em 1 clique.

---

## 📋 Arquitetura da Cadência (Ciclo de 30 Dias)

```
[Cadastro no SaaS] ───> [Fluxo 1: 15 Dias de Teste Grátis]
                             │
                             ├─ Dia 1 ao 14: 1 mensagem/dia (WhatsApp + E-mail)
                             ├─ Dia 15: Término do Teste Grátis
                             │
                             ▼ (Se NÃO assinar)
                        [Fluxo 2: 15 Dias de Pós-Teste & Conversão]
                             │
                             ├─ Dia 16 ao 19: Reengajamento & Quebra de Objeções
                             ├─ Dia 20 (🔥): Promoção 50% OFF (Cupom DOC50OFF - 48h)
                             ├─ Dia 21 ao 29: Urgência e Ofertas Finais
                             ├─ Dia 30: Último contato de despedida
                             │
                             ▼ (Ao final dos 30 dias)
                        [Status alterado para: "Não assinou"]

* Em QUALQUER momento que o lead assinar no checkout, ele sai imediatamente de todos os fluxos ativos.
```

---

## 🛠️ Instalação e Configuração

### 1. Clonar e Instalar Dependências
```bash
composer install
cp .env.example .env
php artisan key:generate
```

### 2. Configurar o Banco de Dados e Migrations
No arquivo `.env`, configure a conexão com o banco de dados e execute as migrações e seeders:
```bash
php artisan migrate --seed
```

O seeder padrão (`DatabaseSeeder`) cria:
1. **Administrador padrão:**
   - **E-mail:** `admin@documentalista.com.br`
   - **Senha:** `admin123456`
2. **Fluxo 1 (15 etapas)** e **Fluxo 2 (15 etapas)** com cópias persuasivas completas, variáveis dinâmicas e oferta de 50% OFF no dia 20 com o cupom `DOC50OFF`.

### 3. Iniciar o Servidor Local
```bash
php artisan serve
```
Acesse o painel em: **[http://localhost:8000/admin](http://localhost:8000/admin)**

---

## ⚙️ Configuração de Produção: Scheduler e Filas

### 1. Laravel Scheduler (Cron)
Adicione a seguinte entrada no `crontab` do servidor (`crontab -e`):
```bash
* * * * * cd /caminho/para/o/projeto && php artisan schedule:run >> /dev/null 2>&1
```

O Scheduler é responsável por:
- Processar etapas devidas a cada minuto (`ProcessDueFlowStepsJob`).
- Transicionar leads expirados de 30 dias para o status "Não assinou" diariamente (`MarkExpiredTrialsJob`).

### 2. Worker de Filas (Supervisor)
Crie um arquivo em `/etc/supervisor/conf.d/documentalista-worker.conf`:
```ini
[program:documentalista-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /caminho/para/o/projeto/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/caminho/para/o/projeto/storage/logs/worker.log
stopwaitsecs=3600
```
Recarregue o Supervisor:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start documentalista-worker:*
```

---

## 🔗 Guia de Integração dos Webhooks

### 1. Webhook de Captação de Leads (Formulário do SaaS)
- **URL do Endpoint:** `POST https://seu-dominio.com.br/api/webhooks/leads`
- **Cabeçalho Opcional de Assinatura:** `X-Signature-SHA256: [hash_hmac_sha256]`
- **Configuração no Painel:** Vá em **Integrações > Formulário de Captação** para definir ou gerar seu `Secret HMAC`.
- **Exemplo de Payload JSON:**
```json
{
  "name": "Dr. Fernando Henrique",
  "email": "fernando@advocacia.com.br",
  "phone": "(11) 98765-4321",
  "saas_user_id": "usr_829103",
  "registered_at": "2026-10-08T20:00:00-03:00",
  "utm_source": "google_ads",
  "utm_medium": "cpc",
  "utm_campaign": "certidoes_imoveis",
  "meta": {
    "empresa": "Henrique Advogados",
    "cidade": "São Paulo"
  }
}
```
*O sistema normaliza automaticamente telefones para o padrão internacional E.164 (`+5511987654321`), deduplica o lead e inicia imediatamente o Fluxo 1.*

---

### 2. WhatsApp Business (Meta Cloud API)
- Vá em **Integrações > WhatsApp Business**:
  - Preencha: `Phone Number ID`, `WhatsApp Business Account ID`, `Access Token` e `Verify Token`.
  - Clique em **"Testar Conexão com a Meta"** para validar o token.
- No painel da Meta Developers:
  - **Callback URL:** `https://seu-dominio.com.br/api/webhooks/whatsapp`
  - **Verify Token:** O mesmo cadastrado no painel.
  - **Campos para Assinar:** `messages` (mensagens e respostas) e `message_deliveries` / `message_status` (status de entrega e leitura).
- **Janela de 24 horas:**
  - Dentro da janela (< 24h desde a última mensagem do lead): envios livres com botões interativos e mídia.
  - Fora da janela (> 24h): utiliza Message Templates aprovados pela Meta.
- **Opt-out Automático:** Respostas contendo "SAIR", "PARAR", "STOP", "CANCELAR" interrompem imediatamente envios de WhatsApp e registram o evento na timeline do lead.

---

### 3. Servidor de E-mail (SMTP Dinâmico)
- Vá em **Integrações > E-mail (SMTP)**:
  - Host, Porta (587 ou 465), Criptografia (TLS/SSL), Usuário e Senha (criptografada).
  - Remetente e Reply-To.
  - Botão **"Testar Conexão Socket/Porta"** e **"Enviar E-mail de Teste Agora"**.
- Todo e-mail enviado possui:
  - Pixel 1x1 transparente para rastrear taxa de leitura (`/t/open/{token}`).
  - Botões CTA rastreáveis (`/t/click/{token}`).
  - Link obrigatório de descadastro em 1 clique em conformidade com a LGPD (`/unsubscribe/{token}`).

---

### 4. Webhook de Conversão (Checkout de Assinatura)
- **URL do Endpoint:** `POST https://seu-dominio.com.br/api/webhooks/subscription`
- Disparado pelo checkout [checkout.v2.e-flips.com.br](https://checkout.v2.e-flips.com.br) ou pela plataforma de pagamentos quando o pagamento da assinatura for confirmado.
- **Payload JSON:**
```json
{
  "lead_id": 12,
  "email": "fernando@advocacia.com.br",
  "plan_name": "Plano Anual Pro",
  "amount": 1490.00,
  "transaction_id": "tx_eflips_998811",
  "subscribed_at": "2026-10-08T22:30:00-03:00"
}
```
*Ao receber, o sistema marca o lead com status "Assinou 🎉", cancela todos os fluxos ativos imediatamente e pontua +20 no Lead Score.*

---

## 🧪 Testes Automatizados

Para rodar todos os testes com cobertura completa:
```bash
php artisan test
```

Suíte de testes incluída:
- `LeadWebhookTest`: Captação via API, normalização E.164, deduplicação e HMAC.
- `WhatsAppWebhookTest`: Verificação de challenge da Meta, status `delivered`/`read` e detecção de Opt-out ("SAIR").
- `EmailTrackingTest`: Rastreamento de pixel 1x1 e descadastro LGPD.
- `FlowAndLinkTrackingTest`: Redirecionamento rastreável com UTMs para checkout e **idempotência estrita** de disparo.
- `SubscriptionConversionTest`: Conversão de assinatura e Job de 30 dias de expiração ("Não assinou").
- `DashboardAndMetricsTest`: Painel Filament e renderização dos widgets analíticos.
