@extends('emails.layouts.base')

@section('subject', 'Tu código de verificación')

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
        Hola <strong style="color:#374151;">{{ $nombreUsuario }}</strong>, usa el código de abajo para verificar tu cuenta.
    </p>

    <!-- Divider -->
    <div style="border-top:2px solid #FAE9D5;margin-bottom:28px;"></div>

    <!-- Código de verificación -->
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:24px;">
        <tr>
            <td align="center">
                <div style="display:inline-block;background:linear-gradient(135deg,#FDF6EE,#FAE9D5);border:2px solid #F3CFA3;border-radius:16px;padding:24px 40px;text-align:center;">
                    <p style="margin:0 0 6px;font-size:11px;color:#A85D18;font-weight:700;letter-spacing:2px;text-transform:uppercase;">Tu código de verificación</p>
                    <p style="margin:0;font-size:42px;font-weight:900;color:#874915;letter-spacing:10px;font-family:monospace;">{{ $codigo }}</p>
                    <p style="margin:8px 0 0;font-size:12px;color:#9CA3AF;">Válido durante <strong>5 minutos</strong></p>
                </div>
            </td>
        </tr>
    </table>

    <!-- Instrucciones -->
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:24px;">
        <tr>
            <td style="background:#EFF6FF;border-radius:12px;padding:18px 22px;border:1px solid #BFDBFE;">
                <p style="margin:0 0 8px;font-size:13px;font-weight:700;color:#1D4ED8;">¿Cómo verificar tu cuenta?</p>
                <p style="margin:0;font-size:13px;color:#1E40AF;line-height:1.6;">
                    Introduce el código de 6 dígitos en la página de verificación que tienes abierta en tu navegador.
                    Si la cerraste, <a href="{{ config('app.url') }}/verify-email" style="color:#1D4ED8;font-weight:600;">haz clic aquí</a>.
                </p>
            </td>
        </tr>
    </table>

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
