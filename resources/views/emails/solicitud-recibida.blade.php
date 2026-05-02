@extends('emails.layouts.base')

@section('subject', 'Hemos recibido tu solicitud')

@section('icon')
    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 009.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 002.25 1.016c.896 0 1.7-.393 2.25-1.016a3.001 3.001 0 003.75.614m-16.5 0a3.004 3.004 0 01-.621-4.72L4.318 3.44A1.5 1.5 0 015.378 3h13.243a1.5 1.5 0 011.06.44l1.19 1.189a3 3 0 01-.621 4.72m-13.5 8.65h3.75a.75.75 0 00.75-.75V13.5a.75.75 0 00-.75-.75H6.75a.75.75 0 00-.75.75v3.75c0 .415.336.75.75.75z"/>
    </svg>
@endsection

@section('content')
    <!-- Greeting -->
    <h1 style="margin:0 0 8px;text-align:center;font-size:24px;font-weight:800;color:#1F2937;letter-spacing:-0.5px;">
        ¡Solicitud recibida!
    </h1>
    <p style="margin:0 0 28px;text-align:center;font-size:15px;color:#6B7280;line-height:1.6;">
        Hola <strong style="color:#374151;">{{ $solicitud->nombre_contacto }}</strong>, hemos recibido tu solicitud para unirte a Rustikan como vendedor.
        Revisaremos tu información y nos pondremos en contacto contigo en un plazo de <strong style="color:#374151;">48 horas</strong>.
    </p>

    <!-- Divider -->
    <div style="border-top:2px solid #FAE9D5;margin-bottom:28px;"></div>

    <!-- Resumen solicitud -->
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:24px;">
        <tr>
            <td style="background:#FDF6EE;border-radius:12px 12px 0 0;padding:12px 20px;border:1px solid #F3CFA3;border-bottom:none;">
                <p style="margin:0;font-size:13px;font-weight:700;color:#A85D18;text-transform:uppercase;letter-spacing:1px;">Resumen de tu solicitud</p>
            </td>
        </tr>
        <tr>
            <td style="border:1px solid #F3CFA3;border-top:none;border-radius:0 0 12px 12px;background:#FFFFFF;padding:0;overflow:hidden;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">

                    <tr style="border-bottom:1px solid #F5EFE6;">
                        <td style="padding:12px 20px;width:40%;">
                            <p style="margin:0;font-size:12px;font-weight:600;color:#9CA3AF;text-transform:uppercase;letter-spacing:0.5px;">Tienda</p>
                        </td>
                        <td style="padding:12px 20px;">
                            <p style="margin:0;font-size:14px;font-weight:600;color:#1F2937;">{{ $solicitud->nombre_tienda }}</p>
                        </td>
                    </tr>

                    <tr style="background:#FEFCFA;border-bottom:1px solid #F5EFE6;">
                        <td style="padding:12px 20px;">
                            <p style="margin:0;font-size:12px;font-weight:600;color:#9CA3AF;text-transform:uppercase;letter-spacing:0.5px;">Categoría</p>
                        </td>
                        <td style="padding:12px 20px;">
                            <p style="margin:0;font-size:14px;color:#374151;">{{ $solicitud->categoria }}</p>
                        </td>
                    </tr>

                    @if($solicitud->municipio)
                    <tr style="border-bottom:1px solid #F5EFE6;">
                        <td style="padding:12px 20px;">
                            <p style="margin:0;font-size:12px;font-weight:600;color:#9CA3AF;text-transform:uppercase;letter-spacing:0.5px;">Municipio</p>
                        </td>
                        <td style="padding:12px 20px;">
                            <p style="margin:0;font-size:14px;color:#374151;">{{ $solicitud->municipio }}</p>
                        </td>
                    </tr>
                    @endif

                    <tr style="background:#FEFCFA;">
                        <td style="padding:12px 20px;">
                            <p style="margin:0;font-size:12px;font-weight:600;color:#9CA3AF;text-transform:uppercase;letter-spacing:0.5px;">Fecha</p>
                        </td>
                        <td style="padding:12px 20px;">
                            <p style="margin:0;font-size:14px;color:#374151;">{{ $solicitud->created_at->format('d/m/Y \a \l\a\s H:i') }}</p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>

    <!-- Próximos pasos -->
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:28px;">
        <tr>
            <td style="background:#EFF6FF;border-radius:12px;padding:20px 24px;border:1px solid #BFDBFE;">
                <p style="margin:0 0 10px;font-size:13px;font-weight:700;color:#1D4ED8;">¿Qué ocurre ahora?</p>
                <p style="margin:0 0 6px;font-size:13px;color:#1E40AF;line-height:1.6;">
                    1. Nuestro equipo revisará tu solicitud en un plazo máximo de 48 horas hábiles.
                </p>
                <p style="margin:0 0 6px;font-size:13px;color:#1E40AF;line-height:1.6;">
                    2. Te contactaremos en este email para confirmar los detalles.
                </p>
                <p style="margin:0;font-size:13px;color:#1E40AF;line-height:1.6;">
                    3. Una vez aprobado, recibirás acceso a tu panel de vendedor.
                </p>
            </td>
        </tr>
    </table>

    <!-- CTA -->
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:24px;">
        <tr>
            <td align="center">
                <a href="{{ config('app.url') }}"
                   style="display:inline-block;background:linear-gradient(135deg,#C97420,#A85D18);color:#FFFFFF;text-decoration:none;font-size:14px;font-weight:700;padding:14px 32px;border-radius:10px;letter-spacing:0.3px;">
                    Visitar Rustikan
                </a>
            </td>
        </tr>
    </table>

    <!-- Contacto -->
    <p style="margin:0;text-align:center;font-size:12px;color:#9CA3AF;line-height:1.6;">
        ¿Tienes alguna pregunta? Responde a este email o contáctanos en
        <a href="mailto:info@rustikan.com" style="color:#C97420;text-decoration:none;">info@rustikan.com</a>
    </p>
@endsection
