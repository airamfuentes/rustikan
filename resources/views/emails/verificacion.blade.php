@extends('emails.layouts.base')

@section('subject', 'Verifica tu correo electrónico')

@section('icon')
    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/>
    </svg>
@endsection

@section('content')
    <!-- Greeting -->
    <h1 style="margin:0 0 8px;text-align:center;font-size:24px;font-weight:800;color:#1F2937;letter-spacing:-0.5px;">
        ¡Bienvenido/a a Rustikan!
    </h1>
    <p style="margin:0 0 28px;text-align:center;font-size:15px;color:#6B7280;line-height:1.6;">
        Hola <strong style="color:#374151;">{{ $nombreUsuario }}</strong>, gracias por unirte a nuestra comunidad de productos locales de Lanzarote.
    </p>

    <!-- Divider -->
    <div style="border-top:2px solid #FAE9D5;margin-bottom:28px;"></div>

    <!-- Info box -->
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:28px;">
        <tr>
            <td style="background:#FDF6EE;border:1px solid #F3CFA3;border-radius:12px;padding:20px 24px;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td width="40" valign="top">
                            <div style="background:#FAE9D5;border-radius:8px;width:36px;height:36px;text-align:center;line-height:36px;font-size:14px;font-weight:700;color:#A85D18;">@</div>
                        </td>
                        <td style="padding-left:14px;vertical-align:top;">
                            <p style="margin:0 0 4px;font-size:14px;font-weight:700;color:#A85D18;">Verifica tu correo electrónico</p>
                            <p style="margin:0;font-size:13px;color:#6B7280;line-height:1.5;">
                                Para activar tu cuenta y empezar a comprar productos artesanales, haz clic en el botón de abajo. El enlace expira en <strong>60 minutos</strong>.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- CTA Button -->
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:28px;">
        <tr>
            <td align="center">
                <a href="{{ $url }}"
                   style="display:inline-block;background:linear-gradient(135deg,#C97420,#A85D18);color:#FFFFFF;text-decoration:none;font-size:16px;font-weight:700;padding:16px 40px;border-radius:12px;letter-spacing:0.3px;box-shadow:0 4px 14px rgba(168,93,24,0.4);">
                    Verificar mi correo electrónico
                </a>
            </td>
        </tr>
    </table>

    <!-- Alternative link -->
    <div style="background:#F9FAFB;border:1px solid #E5E7EB;border-radius:10px;padding:16px 20px;margin-bottom:24px;">
        <p style="margin:0 0 8px;font-size:12px;color:#9CA3AF;">Si el botón no funciona, copia y pega este enlace en tu navegador:</p>
        <p style="margin:0;word-break:break-all;">
            <a href="{{ $url }}" style="font-size:12px;color:#A85D18;text-decoration:underline;">{{ $url }}</a>
        </p>
    </div>

    <!-- Security notice -->
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
        <tr>
            <td style="border-left:3px solid #FED7AA;padding:12px 16px;">
                <p style="margin:0;font-size:12px;color:#9CA3AF;line-height:1.6;">
                    Si no has creado ninguna cuenta en Rustikan, ignora este mensaje. Tu dirección de correo no será utilizada sin tu confirmación.
                </p>
            </td>
        </tr>
    </table>
@endsection
