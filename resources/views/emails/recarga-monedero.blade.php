@extends('emails.layouts.base')

@section('subject', number_format($cantidad, 2, ',', '.') . ' RC añadidos a tu monedero')

@section('icon')
    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="8" cy="8" r="6"/><path d="M18.09 10.37A6 6 0 1 1 10.34 18"/><path d="M7 6h1v4"/><path d="m16.71 13.88.7.71-2.82 2.82"/>
    </svg>
@endsection

@section('content')
    <h1 style="margin:0 0 8px;text-align:center;font-size:24px;font-weight:800;color:#1F2937;letter-spacing:-0.5px;">
        ¡Fondos añadidos!
    </h1>
    <p style="margin:0 0 28px;text-align:center;font-size:15px;color:#6B7280;line-height:1.6;">
        Hola <strong style="color:#374151;">{{ $user->name }}</strong>, tu recarga se ha completado con éxito.
    </p>

    <div style="border-top:2px solid #FAE9D5;margin-bottom:28px;"></div>

    <!-- Cantidad añadida -->
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:24px;">
        <tr>
            <td align="center">
                <div style="display:inline-block;background:linear-gradient(135deg,#FDF6EE,#FAE9D5);border:2px solid #F3CFA3;border-radius:16px;padding:24px 40px;">
                    <p style="margin:0 0 4px;font-size:12px;color:#A85D18;font-weight:600;letter-spacing:2px;text-transform:uppercase;">RustiCoins añadidos</p>
                    <p style="margin:0;font-size:40px;font-weight:800;color:#874915;letter-spacing:-1px;">+{{ number_format($cantidad, 2, ',', '.') }} RC</p>
                    <p style="margin:6px 0 0;font-size:12px;color:#9CA3AF;">1 RC = 1 € · Pago procesado por Stripe</p>
                </div>
            </td>
        </tr>
    </table>

    <!-- Saldo actualizado -->
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:28px;">
        <tr>
            <td style="background:#F0FDF4;border:1px solid #BBF7D0;border-radius:12px;padding:20px 24px;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td>
                            <p style="margin:0 0 2px;font-size:13px;font-weight:700;color:#059669;">Saldo disponible en tu monedero</p>
                            <p style="margin:0;font-size:12px;color:#6B7280;">Úsalo en tu próxima compra en Rustikan.</p>
                        </td>
                        <td align="right" style="white-space:nowrap;padding-left:16px;">
                            <p style="margin:0;font-size:22px;font-weight:800;color:#059669;">{{ number_format($saldoNuevo, 2, ',', '.') }} RC</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Info recarga -->
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:28px;">
        <tr>
            <td style="background:#FFF7ED;border:1px solid #FED7AA;border-radius:12px;padding:16px 20px;">
                <p style="margin:0 0 6px;font-size:13px;font-weight:700;color:#C97420;">¿Cómo usar tus RustiCoins?</p>
                <p style="margin:0;font-size:13px;color:#6B7280;line-height:1.6;">En el paso final de cualquier compra verás la opción de pagar con tu saldo RustiCoin. El descuento se aplica automáticamente.</p>
            </td>
        </tr>
    </table>

    <div style="text-align:center;padding:8px 0 0;">
        <p style="margin:0 0 20px;font-size:15px;color:#6B7280;line-height:1.7;">
            Gracias por confiar en <strong style="color:#A85D18;">Rustikan</strong>.<br>
            Apoya lo local y disfruta los mejores productos de Lanzarote.
        </p>
        <a href="{{ route('monedero.index') }}"
           style="display:inline-block;background:linear-gradient(135deg,#C97420,#A85D18);color:#ffffff;font-size:14px;font-weight:700;text-decoration:none;border-radius:10px;padding:14px 32px;letter-spacing:0.3px;">
            Ver mi monedero
        </a>
    </div>
@endsection
