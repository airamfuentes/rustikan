@extends('pdfs.layout')

@section('titulo', 'Pedidos del almacén')

@section('meta')
    <div class="meta-line">Registros: {{ count($pedidos) }}</div>
    @if($estado !== 'todos')
        <div class="meta-line">Filtro estado: {{ ucfirst(str_replace('_', ' ', $estado)) }}</div>
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
    ];
    $totalGeneral = 0;
@endphp

@section('content')
    <div class="pdf-title">Pedidos activos</div>
    <div class="pdf-subtitle">Listado de pedidos asignados al almacén pendientes de gestión.</div>

    <table>
        <thead>
            <tr>
                <th>Nº Pedido</th>
                <th>Cliente</th>
                <th>Estado</th>
                <th class="text-right">Artículos</th>
                <th class="text-right">Total (€)</th>
                <th>Fecha</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pedidos as $p)
                @php
                    $totalGeneral += (float) $p->total;
                    $cant = $p->items->sum('cantidad');
                @endphp
                <tr>
                    <td><strong>{{ $p->numero_pedido ?? '#'.$p->id }}</strong></td>
                    <td>{{ $p->user->name ?? 'Desconocido' }}</td>
                    <td>
                        <span class="badge {{ $estadoBadge[$p->estado] ?? 'badge-gray' }}">
                            {{ ucfirst(str_replace('_', ' ', $p->estado)) }}
                        </span>
                    </td>
                    <td class="text-right num">{{ $cant }}</td>
                    <td class="text-right num">{{ number_format((float) $p->total, 2, ',', '.') }}</td>
                    <td>{{ $p->created_at->format('d/m/Y H:i') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4">TOTAL</td>
                <td class="text-right num">{{ number_format($totalGeneral, 2, ',', '.') }} €</td>
                <td></td>
            </tr>
        </tfoot>
    </table>
@endsection
