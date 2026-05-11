@extends('pdfs.layout')

@section('titulo', 'Hoja de preparación')

@section('meta')
    <div class="meta-line">Pedido: <strong>{{ $pedido->numero_pedido ?? '#'.$pedido->id }}</strong></div>
    <div class="meta-line">Fecha: {{ $pedido->created_at->format('d/m/Y H:i') }}</div>
@endsection

@php
    $estadoBadge = [
        'pendiente'      => 'badge-yellow',
        'confirmado'     => 'badge-blue',
        'en_preparacion' => 'badge-orange',
        'enviado'        => 'badge-orange',
        'entregado'      => 'badge-green',
        'incidencia'     => 'badge-red',
        'cancelado'      => 'badge-gray',
    ];
@endphp

@section('content')
    <div class="pdf-title">Pedido {{ $pedido->numero_pedido ?? '#'.$pedido->id }}</div>
    <div class="pdf-subtitle">
        Estado actual:
        <span class="badge {{ $estadoBadge[$pedido->estado] ?? 'badge-gray' }}">
            {{ ucfirst(str_replace('_', ' ', $pedido->estado)) }}
        </span>
    </div>

    <div class="summary-grid">
        <div class="summary-card">
            <div class="label">Cliente</div>
            <div class="value" style="font-size:14px;">{{ $pedido->user->name ?? 'Desconocido' }}</div>
            <div style="font-size:11px; color:#9a3412; margin-top:2px;">{{ $pedido->user->email ?? '' }}</div>
        </div>
        <div class="summary-card">
            <div class="label">Teléfono</div>
            <div class="value" style="font-size:14px;">{{ $pedido->telefono_contacto ?? '—' }}</div>
        </div>
        <div class="summary-card">
            <div class="label">Método de pago</div>
            <div class="value" style="font-size:14px; text-transform:capitalize;">{{ $pedido->metodo_pago ?? '—' }}</div>
        </div>
        <div class="summary-card">
            <div class="label">Total</div>
            <div class="value">{{ number_format((float) $pedido->total, 2, ',', '.') }} €</div>
        </div>
    </div>

    <div style="margin-bottom:18px; padding:14px 16px; border:1px solid #e5e7eb; border-radius:10px; background:#f9fafb;">
        <div style="font-size:11px; text-transform:uppercase; color:#6b7280; letter-spacing:.6px; font-weight:600; margin-bottom:4px;">
            Dirección de envío
        </div>
        <div>{{ $pedido->direccion_envio ?? '—' }}</div>
        @if($pedido->notas)
            <div style="margin-top:10px; padding:8px 12px; background:#fffbeb; border:1px solid #fde68a; border-radius:8px; font-size:11.5px; color:#92400e;">
                <strong>Nota del cliente:</strong> {{ $pedido->notas }}
            </div>
        @endif
    </div>

    <h3 style="font-size:14px; font-weight:700; color:#111827; margin-bottom:10px;">Artículos a preparar</h3>

    <table>
        <thead>
            <tr>
                <th>Producto</th>
                <th>Tienda</th>
                <th class="text-right">Cant.</th>
                <th class="text-right">P.Unit (€)</th>
                <th class="text-right">Subtotal (€)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pedido->items as $item)
                <tr>
                    <td><strong>{{ $item->producto_nombre ?? ($item->producto?->nombre ?? '—') }}</strong></td>
                    <td>{{ $item->tienda?->nombre ?? '—' }}</td>
                    <td class="text-right num">{{ $item->cantidad }}</td>
                    <td class="text-right num">{{ number_format((float) $item->precio_unitario, 2, ',', '.') }}</td>
                    <td class="text-right num">{{ number_format((float) $item->subtotal, 2, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2">Subtotal</td>
                <td colspan="3" class="text-right num">{{ number_format((float) $pedido->subtotal, 2, ',', '.') }} €</td>
            </tr>
            <tr>
                <td colspan="2">Gastos de envío</td>
                <td colspan="3" class="text-right num">{{ number_format((float) $pedido->gastos_envio, 2, ',', '.') }} €</td>
            </tr>
            <tr>
                <td colspan="2">TOTAL</td>
                <td colspan="3" class="text-right num">{{ number_format((float) $pedido->total, 2, ',', '.') }} €</td>
            </tr>
        </tfoot>
    </table>
@endsection
