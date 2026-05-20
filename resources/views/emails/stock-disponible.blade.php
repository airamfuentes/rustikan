@extends('emails.layouts.base')

@section('subject', '¡' . $producto->nombre . ' vuelve a estar disponible!')

@section('icon')
<!-- Bell icon -->
<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:block;margin:auto;">
    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
    <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
</svg>
@endsection

@section('content')
<!-- Greeting -->
<p style="margin:0 0 8px;font-size:22px;font-weight:800;color:#1F2937;text-align:center;">¡Vuelve a estar disponible!</p>
<p style="margin:0 0 28px;font-size:14px;color:#6B7280;text-align:center;">Hola {{ $destinatarioNombre }}, el producto que marcaste como favorito ya tiene stock.</p>

<!-- Product highlight -->
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:28px;">
    <tr>
        <td style="background:linear-gradient(135deg,#FEF3C7,#FDE68A);border-radius:12px;padding:20px 24px;">
            <p style="margin:0 0 4px;font-size:11px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:#92400E;">Producto disponible</p>

            @if($producto->imagen)
            <div style="text-align:center;margin-bottom:16px;">
                <img src="{{ $producto->imagen }}" alt="{{ $producto->nombre }}"
                     style="width:100px;height:100px;object-fit:cover;border-radius:10px;border:2px solid rgba(0,0,0,0.08);" />
            </div>
            @endif

            <p style="margin:0 0 6px;font-size:20px;font-weight:800;color:#1F2937;">{{ $producto->nombre }}</p>
            <p style="margin:0 0 10px;font-size:14px;color:#6B7280;">{{ $producto->tienda->nombre ?? 'Rustikan' }}</p>
            <p style="margin:0;font-size:22px;font-weight:900;color:#C97420;">
                {{ number_format($producto->precio_oferta ?? $producto->precio, 2, ',', '.') }} €
                @if($producto->precio_oferta)
                <span style="font-size:14px;color:#9CA3AF;text-decoration:line-through;margin-left:6px;">{{ number_format($producto->precio, 2, ',', '.') }} €</span>
                @endif
                <span style="font-size:13px;color:#6B7280;font-weight:500;margin-left:4px;">/ {{ $producto->unidad }}</span>
            </p>
        </td>
    </tr>
</table>

<!-- Stock info -->
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:28px;">
    <tr>
        <td style="background:#F0FDF4;border-radius:10px;padding:14px 20px;border-left:4px solid #22C55E;">
            <p style="margin:0;font-size:13px;color:#15803D;font-weight:600;">
                ✓ Stock disponible ahora — ¡date prisa antes de que se agote!
            </p>
        </td>
    </tr>
</table>

<!-- CTA -->
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:24px;">
    <tr>
        <td align="center">
            <a href="{{ url('/tienda/' . ($producto->tienda->slug ?? '')) }}"
               style="display:inline-block;background:linear-gradient(135deg,#C97420,#A85D18);color:#FFFFFF;text-decoration:none;font-size:15px;font-weight:700;padding:14px 36px;border-radius:10px;letter-spacing:0.3px;">
                Ver producto →
            </a>
        </td>
    </tr>
</table>

<p style="margin:0;font-size:12px;color:#9CA3AF;text-align:center;line-height:1.6;">
    Si ya no quieres recibir avisos de este producto, puedes desactivarlo<br>desde el carrito o la página del producto.
</p>
@endsection
