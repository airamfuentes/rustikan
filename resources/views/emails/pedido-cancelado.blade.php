@extends('emails.layouts.base')

@section('subject', 'Pedido #' . $pedido->numero_pedido . ' cancelado')

@section('icon')
    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M6 18L18 6M6 6l12 12"/>
    </svg>
@endsection

@section('content')
    <h1 style="margin:0 0 8px;text-align:center;font-size:24px;font-weight:800;color:#1F2937;letter-spacing:-0.5px;">
        Tu pedido ha sido cancelado
    </h1>
    <p style="margin:0 0 28px;text-align:center;font-size:15px;color:#6B7280;line-height:1.6;">
        Hola <strong style="color:#374151;">{{ $pedido->user->name }}</strong>, tu pedido ha sido cancelado correctamente.
    </p>

    <div style="border-top:2px solid #FAE9D5;margin-bottom:28px;"></div>

    <!-- Order number badge -->
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:24px;">
        <tr>
            <td align="center">
                <div style="display:inline-block;background:linear-gradient(135deg,#FEF2F2,#FEE2E2);border:2px solid #FCA5A5;border-radius:12px;padding:14px 28px;">
                    <p style="margin:0 0 2px;font-size:11px;color:#991B1B;font-weight:600;letter-spacing:2px;text-transform:uppercase;">Pedido cancelado</p>
                    <p style="margin:0;font-size:22px;font-weight:800;color:#7F1D1D;letter-spacing:1px;">{{ $pedido->numero_pedido }}</p>
                    <p style="margin:4px 0 0;font-size:11px;color:#9CA3AF;">Cancelado el {{ now()->format('d/m/Y \a \l\a\s H:i') }}</p>
                </div>
            </td>
        </tr>
    </table>

    @if($motivo ?? null)
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:24px;">
            <tr>
                <td style="background:#FFFBEB;border:1px solid #FDE68A;border-radius:12px;padding:16px 20px;">
                    <p style="margin:0 0 6px;font-size:11px;font-weight:700;color:#92400E;text-transform:uppercase;letter-spacing:1px;">Motivo</p>
                    <p style="margin:0;font-size:13px;color:#374151;line-height:1.6;">{{ $motivo }}</p>
                </td>
            </tr>
        </table>
    @endif

    <!-- Reembolso info -->
    @if($tipoReembolso === 'rusticoin')
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:28px;">
            <tr>
                <td style="background:linear-gradient(135deg,#FFF7ED,#FED7AA);border:1px solid #F3CFA3;border-radius:12px;padding:20px 24px;">
                    <p style="margin:0 0 8px;font-size:13px;font-weight:700;color:#9A3412;text-transform:uppercase;letter-spacing:1px;">🪙 Reembolso a tu monedero</p>
                    <p style="margin:0 0 12px;font-size:14px;color:#374151;line-height:1.6;">
                        Hemos abonado <strong style="color:#A85D18;">{{ number_format($pedido->total, 2, ',', '.') }} RustiCoins</strong> a tu monedero. Ya están disponibles para tu próximo pedido.
                    </p>
                    <p style="margin:0;font-size:12px;color:#9CA3AF;">1 RC = 1 €. Puedes consultarlos en tu panel de usuario.</p>
                </td>
            </tr>
        </table>
    @elseif($tipoReembolso === 'tarjeta')
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:28px;">
            <tr>
                <td style="background:#EFF6FF;border:1px solid #BFDBFE;border-radius:12px;padding:20px 24px;">
                    <p style="margin:0 0 8px;font-size:13px;font-weight:700;color:#1E40AF;text-transform:uppercase;letter-spacing:1px;">💳 Reembolso a tarjeta</p>
                    <p style="margin:0 0 12px;font-size:14px;color:#374151;line-height:1.6;">
                        Se procesará el reembolso de <strong style="color:#1E40AF;">{{ number_format($pedido->total, 2, ',', '.') }} €</strong> a tu tarjeta original.
                    </p>
                    <p style="margin:0;font-size:12px;color:#9CA3AF;">El abono puede tardar entre 5 y 7 días hábiles en aparecer en tu cuenta.</p>
                </td>
            </tr>
        </table>
    @else
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:28px;">
            <tr>
                <td style="background:#F9FAFB;border:1px solid #E5E7EB;border-radius:12px;padding:20px 24px;">
                    <p style="margin:0 0 6px;font-size:13px;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:1px;">Importe del pedido</p>
                    <p style="margin:0;font-size:18px;color:#374151;font-weight:800;">{{ number_format($pedido->total, 2, ',', '.') }} €</p>
                </td>
            </tr>
        </table>
    @endif

    <!-- Items resumen -->
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:24px;">
        <tr>
            <td style="background:#F9FAFB;border-radius:12px 12px 0 0;padding:12px 20px;border:1px solid #E5E7EB;border-bottom:none;">
                <p style="margin:0;font-size:13px;font-weight:700;color:#6B7280;text-transform:uppercase;letter-spacing:1px;">Pedido cancelado</p>
            </td>
        </tr>
        <tr>
            <td style="border:1px solid #E5E7EB;border-top:none;border-radius:0 0 12px 12px;overflow:hidden;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                    @foreach($pedido->items as $index => $item)
                    <tr style="background:{{ $index % 2 === 0 ? '#FFFFFF' : '#FAFAFA' }};">
                        <td style="padding:12px 20px;border-bottom:1px solid #F3F4F6;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td>
                                        <p style="margin:0 0 2px;font-size:13px;font-weight:600;color:#6B7280;text-decoration:line-through;">{{ $item->producto_nombre }}</p>
                                        <p style="margin:0;font-size:11px;color:#9CA3AF;">{{ $item->tienda_nombre }} · {{ $item->cantidad }} {{ $item->cantidad > 1 ? 'unidades' : 'unidad' }}</p>
                                    </td>
                                    <td align="right" style="white-space:nowrap;vertical-align:top;">
                                        <p style="margin:0;font-size:13px;color:#9CA3AF;text-decoration:line-through;">{{ number_format($item->subtotal, 2, ',', '.') }} €</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    @endforeach
                </table>
            </td>
        </tr>
    </table>

    <div style="text-align:center;padding:8px 0;">
        <p style="margin:0;font-size:14px;color:#6B7280;line-height:1.7;">
            Lamentamos las molestias. Si tienes alguna duda, escríbenos a
            <a href="mailto:info@rustikan.com" style="color:#A85D18;font-weight:600;text-decoration:none;">info@rustikan.com</a>
        </p>
    </div>
@endsection
