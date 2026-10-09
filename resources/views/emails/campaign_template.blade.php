<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $mailSubject }}</title>
    <style>
        body { margin: 0; padding: 0; background-color: #f8fafc; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #1e293b; -webkit-font-smoothing: antialiased; }
        .wrapper { width: 100%; table-layout: fixed; background-color: #f8fafc; padding: 30px 0; }
        .main-card { background-color: #ffffff; margin: 0 auto; max-width: 600px; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); }
        .header { background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); padding: 28px 32px; text-align: left; }
        .logo-text { color: #ffffff; font-size: 20px; font-weight: 700; letter-spacing: -0.5px; margin: 0; }
        .logo-badge { color: #38bdf8; font-weight: 400; font-size: 13px; margin-left: 6px; }
        .content { padding: 36px 32px; font-size: 15px; line-height: 1.65; color: #334155; }
        .content p { margin: 0 0 16px; }
        .hero-image { width: 100%; max-width: 100%; border-radius: 8px; margin-bottom: 24px; }
        .cta-container { text-align: center; margin: 32px 0 24px; }
        .cta-button { display: inline-block; background-color: #0284c7; color: #ffffff !important; text-decoration: none; font-weight: 600; font-size: 15px; padding: 14px 28px; border-radius: 8px; box-shadow: 0 4px 12px rgba(2, 132, 199, 0.25); }
        .footer { background-color: #f1f5f9; padding: 24px 32px; text-align: center; font-size: 12px; color: #64748b; border-top: 1px solid #e2e8f0; }
        .footer a { color: #64748b; text-decoration: underline; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="main-card">
            <!-- Header -->
            <div class="header">
                <h1 class="logo-text">
                    Documentalista <span class="logo-badge">SaaS</span>
                </h1>
            </div>

            <!-- Conteúdo -->
            <div class="content">
                @if(!empty($imagePath))
                    <img src="{{ url($imagePath) }}" alt="Documentalista" class="hero-image" />
                @endif

                {!! $bodyHtml !!}

                @if(!empty($buttonLabel) && !empty($buttonUrl))
                    <div class="cta-container">
                        <a href="{{ $buttonUrl }}" class="cta-button" target="_blank">
                            {{ $buttonLabel }}
                        </a>
                    </div>
                @endif
            </div>

            <!-- Rodapé com LGPD & Descadastro -->
            <div class="footer">
                <p style="margin: 0 0 8px;">
                    Você está recebendo este e-mail porque se cadastrou para testar o <strong>Documentalista</strong>.
                </p>
                <p style="margin: 0;">
                    Documentalista Tecnologia S/A &bull; Todos os direitos reservados. &bull;
                    @if(!empty($unsubscribeToken))
                        <a href="{{ url('/unsubscribe/' . $unsubscribeToken) }}">Descadastrar da lista de e-mails</a>
                    @endif
                </p>
            </div>
        </div>
    </div>

    <!-- Pixel 1x1 Invisível de Rastreamento de Abertura -->
    @if(!empty($openTrackingToken))
        <img src="{{ url('/t/open/' . $openTrackingToken) }}" width="1" height="1" alt="" style="display:none; width:1px; height:1px; border:0;" />
    @endif
</body>
</html>
