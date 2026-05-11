@extends('pdfs.layout')

@section('titulo', 'Listado de pedidos')

@section('meta')
    <div class="meta-line">Registros: {{ count($pedidos) }}</div>
    @if($estado !== 'todos')
        <div class="meta-line">Filtro estado: {{ ucfirst($estado) }}</div>
    @endif
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
    <div class="pdf-title">Pedidos</div>
    <div class="pdf-subtitle">Listado de pedidos registrados en la plataforma.</div>

    <table>
        <thead>
            <tr>
                <th>Nº Pedido</th>
                <th>Cliente</th>
                <th>Email</th>
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
                    <td>{{ $p->user->email ?? '' }}</td>
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
                <td colspan="4">TOTAL FACTURADO</td>
                <td class="text-right num">{{ number_format($totalGeneral, 2, ',', '.') }} €</td>
                <td></td>
            </tr>
        </tfoot>
    </table>
@endsection
