@extends('emails.layouts.base')

@section('subject', 'Actualización sobre tu candidatura')

@section('icon')
    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
        <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
    </svg>
@endsection

@section('content')
    <!-- Greeting -->
    <h1 style="margin:0 0 8px;text-align:center;font-size:24px;font-weight:800;color:#1F2937;letter-spacing:-0.5px;">
        Gracias por tu candidatura
    </h1>
    <p style="margin:0 0 28px;text-align:center;font-size:15px;color:#6B7280;line-height:1.6;">
        Hola <strong style="color:#374151;">{{ $solicitud->nombre }}</strong>, te escribimos para darte una respuesta sobre tu candidatura para el puesto de
        <strong style="color:#374151;">{{ $puestoLabel }}</strong>.
    </p>

    <!-- Divider -->
    <div style="border-top:2px solid #FAE9D5;margin-bottom:28px;"></div>

    <!-- Mensaje principal -->
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:28px;">
        <tr>
            <td style="background:#FFFBEB;border-radius:12px;padding:24px;border:1px solid #FDE68A;">
                <p style="margin:0 0 12px;font-size:14px;color:#92400E;line-height:1.7;">
                    Tras revisar tu CV con detenimiento, hemos decidido <strong>no continuar con tu candidatura</strong> en esta ocasión. La decisión no significa que tu perfil no sea valioso: simplemente, en este momento estamos buscando un encaje algo distinto al que tu trayectoria refleja.
                </p>
                <p style="margin:0;font-size:14px;color:#92400E;line-height:1.7;">
                    Sabemos que recibir esta respuesta nunca es agradable, y queremos agradecerte de verdad el tiempo y la confianza que has depositado en Rustikan al enviarnos tu CV.
                </p>
            </td>
        </tr>
    </table>

    <!-- Próximos pasos / consejos -->
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:28px;">
        <tr>
            <td style="background:#EFF6FF;border-radius:12px;padding:20px 24px;border:1px solid #BFDBFE;">
                <p style="margin:0 0 10px;font-size:13px;font-weight:700;color:#1D4ED8;">Algunas ideas para seguir adelante</p>
                <p style="margin:0 0 6px;font-size:13px;color:#1E40AF;line-height:1.6;">
                    · Si en el futuro abrimos un puesto que encaje mejor contigo, te avisaremos personalmente.
                </p>
                <p style="margin:0 0 6px;font-size:13px;color:#1E40AF;line-height:1.6;">
                    · Te animamos a seguirnos en redes y suscribirte a nuestras novedades para enterarte de nuevas vacantes.
                </p>
                <p style="margin:0;font-size:13px;color:#1E40AF;line-height:1.6;">
                    · Y, si quieres, puedes volver a presentarte en otra convocatoria — todas las candidaturas se valoran de cero.
                </p>
            </td>
        </tr>
    </table>

    <!-- Mensaje cálido de cierre -->
    <p style="margin:0 0 24px;text-align:center;font-size:14px;color:#6B7280;line-height:1.7;font-style:italic;">
        Te deseamos mucha suerte en tu búsqueda. Estamos seguros de que pronto encontrarás un proyecto donde tu talento marque la diferencia.
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
        Si quieres preguntarnos cualquier cosa sobre esta decisión, escríbenos a
        <a href="mailto:info@rustikan.com" style="color:#C97420;text-decoration:none;">info@rustikan.com</a>
    </p>
@endsection
