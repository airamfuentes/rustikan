@extends('pdfs.layout')

@section('titulo', 'Historial de pedidos')

@section('meta')
    <div class="meta-line">Registros: {{ count($pedidos) }}</div>
    @if($estado)
        <div class="meta-line">Filtro estado: {{ ucfirst(str_replace('_', ' ', $estado)) }}</div>
    @endif
    @if($fecha_desde || $fecha_hasta)
        <div class="meta-line">Período: {{ $fecha_desde ?? '—' }} → {{ $fecha_hasta ?? '—' }}</div>
    @endif
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
    $totalGeneral = 0;
@endphp

@section('content')
    <div class="pdf-title">Historial de pedidos</div>
    <div class="pdf-subtitle">Listado completo de pedidos gestionados por el almacén.</div>

    <table>
        <thead>
            <tr>
                <th>Nº Pedido</th>
                <th>Cliente</th>
                <th>Estado</th>
                <th class="text-right">Total (€)</th>
                <th>Fecha</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pedidos as $p)
                @php $totalGeneral += (float) $p->total; @endphp
                <tr>
                    <td><strong>{{ $p->numero_pedido ?? '#'.$p->id }}</strong></td>
                    <td>{{ $p->user->name ?? 'Desconocido' }}</td>
                    <td>
                        <span class="badge {{ $estadoBadge[$p->estado] ?? 'badge-gray' }}">
                            {{ ucfirst(str_replace('_', ' ', $p->estado)) }}
                        </span>
                    </td>
                    <td class="text-right num">{{ number_format((float) $p->total, 2, ',', '.') }}</td>
                    <td>{{ $p->created_at->format('d/m/Y H:i') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3">TOTAL FACTURADO</td>
                <td class="text-right num">{{ number_format($totalGeneral, 2, ',', '.') }} €</td>
                <td></td>
            </tr>
        </tfoot>
    </table>
@endsection
