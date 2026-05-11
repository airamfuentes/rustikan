@extends('pdfs.layout')

@section('titulo', 'Ingresos por tienda')

@section('meta')
    <div class="meta-line">Período: {{ $desde }} → {{ $hasta }}</div>
@endsection

@php
    $totalBruto = 0; $totalPedidos = 0;
@endphp

@section('content')
    <div class="pdf-title">Ingresos por tienda</div>
    <div class="pdf-subtitle">Facturación bruta y neta de cada tienda con pedidos entregados.</div>

    <table>
        <thead>
            <tr>
                <th>Tienda</th>
                <th class="text-right">Ingresos Brutos (€)</th>
                <th class="text-right">Comisión 10% (€)</th>
                <th class="text-right">Beneficio Neto (€)</th>
                <th class="text-right">Nº Pedidos</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $r)
                @php
                    $bruto = (float) $r->total_ingresos;
                    $com = round($bruto * 0.10, 2);
                    $neto = round($bruto - $com, 2);
                    $totalBruto += $bruto;
                    $totalPedidos += (int) $r->total_pedidos;
                @endphp
                <tr>
                    <td><strong>{{ $r->nombre }}</strong></td>
                    <td class="text-right num">{{ number_format($bruto, 2, ',', '.') }}</td>
                    <td class="text-right num">{{ number_format($com, 2, ',', '.') }}</td>
                    <td class="text-right num">{{ number_format($neto, 2, ',', '.') }}</td>
                    <td class="text-right num">{{ $r->total_pedidos }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            @php
                $totalCom = round($totalBruto * 0.10, 2);
                $totalNet = round($totalBruto - $totalCom, 2);
            @endphp
            <tr>
                <td>TOTAL</td>
                <td class="text-right num">{{ number_format($totalBruto, 2, ',', '.') }}</td>
                <td class="text-right num">{{ number_format($totalCom, 2, ',', '.') }}</td>
                <td class="text-right num">{{ number_format($totalNet, 2, ',', '.') }}</td>
                <td class="text-right num">{{ $totalPedidos }}</td>
            </tr>
        </tfoot>
    </table>
@endsection
