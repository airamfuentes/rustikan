@extends('emails.layouts.base')

@section('subject', '¡Pedido #' . $pedido->numero_pedido . ' confirmado!')

@section('icon')
    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
@endsection

@section('content')
    <!-- Greeting -->
    <h1 style="margin:0 0 8px;text-align:center;font-size:24px;font-weight:800;color:#1F2937;letter-spacing:-0.5px;">
        ¡Tu pedido está confirmado!
    </h1>
    <p style="margin:0 0 28px;text-align:center;font-size:15px;color:#6B7280;line-height:1.6;">
        Hola <strong style="color:#374151;">{{ $pedido->user->name }}</strong>, hemos recibido tu pedido y ya está siendo procesado.
    </p>

    <!-- Divider -->
    <div style="border-top:2px solid #FAE9D5;margin-bottom:28px;"></div>

    <!-- Order number badge -->
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:24px;">
        <tr>
            <td align="center">
                <div style="display:inline-block;background:linear-gradient(135deg,#FDF6EE,#FAE9D5);border:2px solid #F3CFA3;border-radius:12px;padding:14px 28px;">
                    <p style="margin:0 0 2px;font-size:11px;color:#A85D18;font-weight:600;letter-spacing:2px;text-transform:uppercase;">Número de pedido</p>
                    <p style="margin:0;font-size:22px;font-weight:800;color:#874915;letter-spacing:1px;">{{ $pedido->numero_pedido }}</p>
                    <p style="margin:4px 0 0;font-size:11px;color:#9CA3AF;">{{ $pedido->created_at->format('d/m/Y \a \l\a\s H:i') }}</p>
                </div>
            </td>
        </tr>
    </table>

    <!-- Items table -->
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:24px;">
        <tr>
            <td style="background:#FDF6EE;border-radius:12px 12px 0 0;padding:12px 20px;border:1px solid #F3CFA3;border-bottom:none;">
                <p style="margin:0;font-size:13px;font-weight:700;color:#A85D18;text-transform:uppercase;letter-spacing:1px;">Artículos del pedido</p>
            </td>
        </tr>
        <tr>
            <td style="border:1px solid #F3CFA3;border-top:none;border-radius:0 0 12px 12px;overflow:hidden;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                    @foreach($pedido->items as $index => $item)
                    <tr style="background:{{ $index % 2 === 0 ? '#FFFFFF' : '#FEFCFA' }};">
                        <td style="padding:14px 20px;border-bottom:1px solid #F5EFE6;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td>
                                        <p style="margin:0 0 2px;font-size:14px;font-weight:600;color:#1F2937;">{{ $item->producto_nombre }}</p>
                                        <p style="margin:0;font-size:12px;color:#9CA3AF;">{{ $item->tienda_nombre }} · {{ $item->cantidad }} {{ $item->cantidad > 1 ? 'unidades' : 'unidad' }}</p>
                                    </td>
                                    <td align="right" style="white-space:nowrap;vertical-align:top;">
                                        <p style="margin:0 0 2px;font-size:14px;font-weight:700;color:#374151;">{{ number_format($item->subtotal, 2, ',', '.') }} €</p>
                                        <p style="margin:0;font-size:11px;color:#9CA3AF;">{{ number_format($item->precio_unitario, 2, ',', '.') }} € / ud.</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    @endforeach

                    <!-- Totals -->
                    <tr style="background:#F9FAFB;">
                        <td style="padding:12px 20px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td><p style="margin:0 0 6px;font-size:13px;color:#6B7280;">Subtotal</p></td>
                                    <td align="right"><p style="margin:0 0 6px;font-size:13px;color:#374151;">{{ number_format($pedido->subtotal, 2, ',', '.') }} €</p></td>
                                </tr>
                                <tr>
                                    <td>
                                        <p style="margin:0 0 10px;font-size:13px;color:#6B7280;">
                                            Gastos de envío
                                            @if($pedido->gastos_envio == 0)
                                                <span style="background:#D1FAE5;color:#059669;font-size:10px;font-weight:700;padding:2px 8px;border-radius:20px;margin-left:6px;">GRATIS</span>
                                            @endif
                                        </p>
                                    </td>
                                    <td align="right"><p style="margin:0 0 10px;font-size:13px;color:#374151;">{{ $pedido->gastos_envio > 0 ? number_format($pedido->gastos_envio, 2, ',', '.') . ' €' : '0,00 €' }}</p></td>
                                </tr>
                                <tr>
                                    <td style="border-top:2px solid #E5E7EB;padding-top:10px;">
                                        <p style="margin:0;font-size:16px;font-weight:800;color:#1F2937;">TOTAL</p>
                                    </td>
                                    <td align="right" style="border-top:2px solid #E5E7EB;padding-top:10px;">
                                        <p style="margin:0;font-size:18px;font-weight:800;color:#A85D18;">{{ number_format($pedido->total, 2, ',', '.') }} €</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Delivery info -->
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:28px;">
        <tr>
            <td style="background:#F0FDF4;border:1px solid #BBF7D0;border-radius:12px;padding:20px 24px;">
                <p style="margin:0 0 12px;font-size:13px;font-weight:700;color:#059669;text-transform:uppercase;letter-spacing:1px;">Datos de entrega</p>
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td width="50%" style="vertical-align:top;padding-right:12px;">
                            <p style="margin:0 0 4px;font-size:11px;font-weight:600;color:#9CA3AF;text-transform:uppercase;letter-spacing:1px;">Dirección</p>
                            <p style="margin:0;font-size:13px;color:#374151;line-height:1.5;">{{ $pedido->direccion_envio }}</p>
                        </td>
                        <td width="50%" style="vertical-align:top;padding-left:12px;">
                            <p style="margin:0 0 4px;font-size:11px;font-weight:600;color:#9CA3AF;text-transform:uppercase;letter-spacing:1px;">Teléfono</p>
                            <p style="margin:0;font-size:13px;color:#374151;">{{ $pedido->telefono_contacto }}</p>
                        </td>
                    </tr>
                    @if($pedido->notas)
                    <tr>
                        <td colspan="2" style="padding-top:12px;">
                            <p style="margin:0 0 4px;font-size:11px;font-weight:600;color:#9CA3AF;text-transform:uppercase;letter-spacing:1px;">Notas</p>
                            <p style="margin:0;font-size:13px;color:#374151;line-height:1.5;">{{ $pedido->notas }}</p>
                        </td>
                    </tr>
                    @endif
                </table>
            </td>
        </tr>
    </table>

    <!-- Estado badge -->
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:28px;">
        <tr>
            <td style="background:#FFF7ED;border:1px solid #FED7AA;border-radius:12px;padding:16px 20px;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td>
                            <p style="margin:0 0 4px;font-size:13px;font-weight:700;color:#C97420;">Estado del pedido</p>
                            <p style="margin:0;font-size:12px;color:#9CA3AF;line-height:1.5;">Te notificaremos cuando tu pedido sea enviado. Puedes hacer seguimiento desde tu panel de usuario.</p>
                        </td>
                        <td align="right" style="white-space:nowrap;padding-left:16px;">
                            <span style="display:inline-block;background:#FED7AA;color:#9A3412;font-size:12px;font-weight:700;padding:6px 14px;border-radius:20px;text-transform:uppercase;letter-spacing:0.5px;">Pendiente</span>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Thank you message -->
    <div style="text-align:center;padding:8px 0;">
        <p style="margin:0;font-size:15px;color:#6B7280;line-height:1.7;">
            Gracias por apoyar los <strong style="color:#A85D18;">productos locales de Lanzarote</strong>.<br>
            Tu compra hace posible que los productores de la isla sigan adelante.
        </p>
    </div>

    <!-- Invoice button -->
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top:28px;">
        <tr>
            <td align="center">
                <a href="{{ route('factura.show', $pedido->id) }}"
                   style="display:inline-block;background:linear-gradient(135deg,#ea580c,#f97316);color:#ffffff;font-size:14px;font-weight:700;text-decoration:none;border-radius:10px;padding:14px 32px;letter-spacing:0.3px;">
                    Ver / Descargar factura
                </a>
                <p style="margin:10px 0 0;font-size:11px;color:#9CA3AF;">Puedes imprimir o guardar la factura como PDF desde esa página.</p>
            </td>
        </tr>
    </table>
@endsection
