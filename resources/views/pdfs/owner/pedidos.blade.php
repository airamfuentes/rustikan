@extends('pdfs.layout')

@section('titulo', 'Pedidos recibidos')

@section('meta')
    <div class="meta-line">Tienda: {{ $tienda->nombre }}</div>
    <div class="meta-line">Registros: {{ count($items) }}</div>
@endsection

@php
    $estadoBadge = [
        'pendiente'    => 'badge-yellow',
        'confirmado'   => 'badge-blue',
        'preparando'   => 'badge-orange',
        'en_camino'    => 'badge-orange',
        'entregado'    => 'badge-green',
        'cancelado'    => 'badge-red',
    ];
    $totalGeneral = 0;
@endphp

@section('content')
    <div class="pdf-title">Pedidos recibidos — {{ $tienda->nombre }}</div>
    <div class="pdf-subtitle">Listado completo de líneas de pedido vendidas a través de la plataforma.</div>

    <table>
        <thead>
            <tr>
                <th>Nº Pedido</th>
                <th>Producto</th>
                <th>Cliente</th>
                <th>Estado</th>
                <th class="text-right">Cant.</th>
                <th class="text-right">P.Unit (€)</th>
                <th class="text-right">Subtotal (€)</th>
                <th>Fecha</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
                @php $totalGeneral += (float) $item->subtotal; @endphp
                <tr>
                    <td><strong>{{ $item->pedido ? ($item->pedido->numero_pedido ?? '#'.$item->pedido_id) : '#'.$item->pedido_id }}</strong></td>
                    <td>{{ $item->producto_nombre ?? ($item->producto?->nombre ?? '—') }}</td>
                    <td>{{ $item->pedido?->user?->name ?? 'Desconocido' }}</td>
                    <td>
                        <span class="badge {{ $estadoBadge[$item->pedido?->estado] ?? 'badge-gray' }}">
                            {{ ucfirst(str_replace('_', ' ', $item->pedido?->estado ?? '—')) }}
                        </span>
                    </td>
                    <td class="text-right num">{{ $item->cantidad }}</td>
                    <td class="text-right num">{{ number_format((float) $item->precio_unitario, 2, ',', '.') }}</td>
                    <td class="text-right num">{{ number_format((float) $item->subtotal, 2, ',', '.') }}</td>
                    <td>{{ $item->created_at->format('d/m/Y H:i') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6">TOTAL VENDIDO</td>
                <td class="text-right num">{{ number_format($totalGeneral, 2, ',', '.') }} €</td>
                <td></td>
            </tr>
        </tfoot>
    </table>
@endsection
