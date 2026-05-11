@extends('pdfs.layout')

@section('titulo', 'Beneficios mensuales')

@section('meta')
    <div class="meta-line">Tienda: {{ $tienda->nombre }}</div>
    <div class="meta-line">Comisión plataforma: {{ $comisionPct }}%</div>
@endsection

@php
    $totalBruto = 0; $totalPedidos = 0;
@endphp

@section('content')
    <div class="pdf-title">Beneficios — {{ $tienda->nombre }}</div>
    <div class="pdf-subtitle">Desglose mensual de ingresos brutos, comisión y beneficio neto.</div>

    <table>
        <thead>
            <tr>
                <th>Mes</th>
                <th class="text-right">Ingresos Brutos (€)</th>
                <th class="text-right">Comisión {{ $comisionPct }}% (€)</th>
                <th class="text-right">Beneficio Neto (€)</th>
                <th class="text-right">Nº Pedidos</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $r)
                @php
                    $bruto = (float) $r->bruto;
                    $com = round($bruto * $comisionPct / 100, 2);
                    $neto = round($bruto - $com, 2);
                    $totalBruto += $bruto;
                    $totalPedidos += (int) $r->pedidos;
                @endphp
                <tr>
                    <td>{{ $r->mes }}</td>
                    <td class="text-right num">{{ number_format($bruto, 2, ',', '.') }}</td>
                    <td class="text-right num">{{ number_format($com, 2, ',', '.') }}</td>
                    <td class="text-right num">{{ number_format($neto, 2, ',', '.') }}</td>
                    <td class="text-right num">{{ $r->pedidos }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            @php
                $totalCom = round($totalBruto * $comisionPct / 100, 2);
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
