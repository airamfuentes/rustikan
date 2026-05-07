@extends('emails.layouts.base')

@section('subject', 'Hemos recibido tu candidatura')

@section('icon')
    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
        <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
    </svg>
@endsection

@section('content')
    <!-- Greeting -->
    <h1 style="margin:0 0 8px;text-align:center;font-size:24px;font-weight:800;color:#1F2937;letter-spacing:-0.5px;">
        ¡Candidatura recibida!
    </h1>
    <p style="margin:0 0 28px;text-align:center;font-size:15px;color:#6B7280;line-height:1.6;">
        Hola <strong style="color:#374151;">{{ $solicitud->nombre }}</strong>, hemos recibido tu candidatura para trabajar con nosotros en Rustikan.
        Si tu perfil encaja con lo que buscamos, nos pondremos en contacto contigo en un plazo aproximado de <strong style="color:#374151;">7 días hábiles</strong>.
    </p>

    <!-- Divider -->
    <div style="border-top:2px solid #FAE9D5;margin-bottom:28px;"></div>

    <!-- Resumen candidatura -->
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:24px;">
        <tr>
            <td style="background:#FDF6EE;border-radius:12px 12px 0 0;padding:12px 20px;border:1px solid #F3CFA3;border-bottom:none;">
                <p style="margin:0;font-size:13px;font-weight:700;color:#A85D18;text-transform:uppercase;letter-spacing:1px;">Resumen de tu candidatura</p>
            </td>
        </tr>
        <tr>
            <td style="border:1px solid #F3CFA3;border-top:none;border-radius:0 0 12px 12px;background:#FFFFFF;padding:0;overflow:hidden;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">

                    <tr style="border-bottom:1px solid #F5EFE6;">
                        <td style="padding:12px 20px;width:40%;">
                            <p style="margin:0;font-size:12px;font-weight:600;color:#9CA3AF;text-transform:uppercase;letter-spacing:0.5px;">Candidato</p>
                        </td>
                        <td style="padding:12px 20px;">
                            <p style="margin:0;font-size:14px;font-weight:600;color:#1F2937;">{{ $solicitud->nombre }} {{ $solicitud->apellidos }}</p>
                        </td>
                    </tr>

                    <tr style="background:#FEFCFA;border-bottom:1px solid #F5EFE6;">
                        <td style="padding:12px 20px;">
                            <p style="margin:0;font-size:12px;font-weight:600;color:#9CA3AF;text-transform:uppercase;letter-spacing:0.5px;">Puesto</p>
                        </td>
                        <td style="padding:12px 20px;">
                            <p style="margin:0;font-size:14px;color:#374151;">{{ $puestoLabel }}</p>
                        </td>
                    </tr>

                    @if($solicitud->cv_nombre_original)
                    <tr style="border-bottom:1px solid #F5EFE6;">
                        <td style="padding:12px 20px;">
                            <p style="margin:0;font-size:12px;font-weight:600;color:#9CA3AF;text-transform:uppercase;letter-spacing:0.5px;">CV adjunto</p>
                        </td>
                        <td style="padding:12px 20px;">
                            <p style="margin:0;font-size:14px;color:#374151;">{{ $solicitud->cv_nombre_original }}</p>
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
                    1. Revisaremos tu CV y tu mensaje con detenimiento.
                </p>
                <p style="margin:0 0 6px;font-size:13px;color:#1E40AF;line-height:1.6;">
                    2. Si tu perfil encaja con lo que estamos buscando, te contactaremos por email o teléfono.
                </p>
                <p style="margin:0;font-size:13px;color:#1E40AF;line-height:1.6;">
                    3. Si finalmente no podemos avanzar, también te avisaremos para que no quedes en espera.
                </p>
            </td>
        </tr>
    </table>

    <!-- Mensaje motivador -->
    <p style="margin:0 0 24px;text-align:center;font-size:14px;color:#6B7280;line-height:1.6;font-style:italic;">
        Gracias por interesarte en formar parte del equipo de Rustikan. Construir el comercio local de Lanzarote es un trabajo en equipo, y agradecemos cada candidatura que recibimos.
    </p>

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
        ¿Necesitas modificar algo de tu candidatura? Responde a este email o escríbenos a
        <a href="mailto:info@rustikan.com" style="color:#C97420;text-decoration:none;">info@rustikan.com</a>
    </p>
@endsection
