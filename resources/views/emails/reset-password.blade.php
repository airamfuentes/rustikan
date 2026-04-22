@extends('emails.layouts.base')

@section('subject', 'Recupera tu contraseña')

@section('icon')
    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z"/>
    </svg>
@endsection

@section('content')
    <!-- Greeting -->
    <h1 style="margin:0 0 8px;text-align:center;font-size:24px;font-weight:800;color:#1F2937;letter-spacing:-0.5px;">
        Restablecer contraseña
    </h1>
    <p style="margin:0 0 28px;text-align:center;font-size:15px;color:#6B7280;line-height:1.6;">
        Hola <strong style="color:#374151;">{{ $nombreUsuario }}</strong>, hemos recibido una solicitud para restablecer la contraseña de tu cuenta.
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
                            <div style="background:#FAE9D5;border-radius:8px;width:36px;height:36px;text-align:center;line-height:36px;font-size:18px;">🔑</div>
                        </td>
                        <td style="padding-left:14px;vertical-align:top;">
                            <p style="margin:0 0 4px;font-size:14px;font-weight:700;color:#A85D18;">Enlace de recuperación</p>
                            <p style="margin:0;font-size:13px;color:#6B7280;line-height:1.5;">
                                Haz clic en el botón de abajo para crear una nueva contraseña. El enlace es válido durante <strong>60 minutos</strong>.
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
                    🔐 &nbsp; Restablecer mi contraseña
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
                    🔒 Si no has solicitado ningún cambio de contraseña, ignora este mensaje. Tu cuenta permanece segura y sin cambios.
                </p>
            </td>
        </tr>
    </table>
@endsection
