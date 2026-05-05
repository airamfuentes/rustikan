<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Factura #{{ $pedido->id }} — Rustikan</title>
    <style>
        /* ── Reset ── */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        /* ── Base ── */
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            font-size: 13px;
            color: #1f2937;
            background: #f3f4f6;
            padding: 24px;
        }

        /* ── Factura ── */
        .invoice {
            max-width: 800px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 4px 32px rgba(0,0,0,.10);
            overflow: hidden;
        }

        /* ── Header ── */
        .invoice-header {
            background: linear-gradient(135deg, #ea580c 0%, #f97316 60%, #fb923c 100%);
            color: white;
            padding: 36px 40px 28px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        .brand-name {
            font-size: 28px;
            font-weight: 800;
            letter-spacing: -0.5px;
        }
        .brand-tagline {
            font-size: 11px;
            opacity: .85;
            margin-top: 2px;
            font-style: italic;
        }
        .invoice-meta { text-align: right; }
        .invoice-meta h1 { font-size: 22px; font-weight: 700; }
        .invoice-meta .num { font-size: 15px; opacity: .9; margin-top: 4px; }
        .badge-estado {
            display: inline-block;
            margin-top: 8px;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .4px;
            text-transform: uppercase;
            background: rgba(255,255,255,.25);
            backdrop-filter: blur(4px);
        }

        /* ── Info section ── */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .info-box {
            padding: 24px 40px;
            border-right: 1px solid #e5e7eb;
        }
        .info-box:last-child { border-right: none; }
        .info-label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: .8px;
            color: #9ca3af;
            font-weight: 600;
            margin-bottom: 8px;
        }
        .info-box strong {
            display: block;
            font-size: 14px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 3px;
        }
        .info-box span {
            display: block;
            color: #6b7280;
            font-size: 12px;
            line-height: 1.5;
        }

        /* ── Items table ── */
        .items-section { padding: 28px 40px; }
        .section-title {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: .8px;
            color: #9ca3af;
            font-weight: 600;
            margin-bottom: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        thead th {
            background: #f9fafb;
            padding: 10px 14px;
            text-align: left;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .5px;
            color: #6b7280;
            font-weight: 600;
            border-bottom: 2px solid #e5e7eb;
        }
        thead th:last-child, thead th:nth-child(2), thead th:nth-child(3) { text-align: right; }
        tbody td {
            padding: 12px 14px;
            border-bottom: 1px solid #f3f4f6;
            vertical-align: middle;
            color: #374151;
        }
        tbody td:last-child, tbody td:nth-child(2), tbody td:nth-child(3) { text-align: right; }
        tbody tr:last-child td { border-bottom: none; }
        .item-name { font-weight: 600; color: #111827; }
        .item-store { font-size: 11px; color: #9ca3af; margin-top: 2px; }

        /* ── Totals ── */
        .totals { padding: 0 40px 28px; }
        .totals-inner {
            margin-left: auto;
            width: 260px;
            border-top: 2px solid #e5e7eb;
            padding-top: 16px;
        }
        .totals-row {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            font-size: 13px;
            color: #6b7280;
        }
        .totals-row.grand {
            border-top: 2px solid #e5e7eb;
            margin-top: 8px;
            padding-top: 10px;
            font-size: 16px;
            font-weight: 800;
            color: #111827;
        }
        .totals-row.grand span:last-child { color: #ea580c; }

        /* ── Footer ── */
        .invoice-footer {
            background: #f9fafb;
            border-top: 1px solid #e5e7eb;
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 11px;
            color: #9ca3af;
        }
        .footer-brand { font-weight: 700; color: #ea580c; font-size: 13px; }

        /* ── Print button (screen only) ── */
        .print-actions {
            max-width: 800px;
            margin: 0 auto 16px;
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }
        .btn-print {
            background: #ea580c;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .btn-back {
            background: #f3f4f6;
            color: #374151;
            border: 1px solid #e5e7eb;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* ── Media print ── */
        @media print {
            body { background: white; padding: 0; }
            .invoice { box-shadow: none; border-radius: 0; }
            .print-actions { display: none; }
            @page { margin: 1cm; }
        }
    </style>
</head>
<body>
    <!-- Botones visibles solo en pantalla -->
    <div class="print-actions">
        <a href="javascript:history.back()" class="btn-back">
            ← Volver
        </a>
        <button class="btn-print" onclick="window.print()">
            🖨️ Imprimir / Guardar PDF
        </button>
    </div>

    <div class="invoice">
        <!-- Cabecera -->
        <div class="invoice-header">
            <div>
                <div class="brand-name">🌿 Rustikan</div>
                <div class="brand-tagline">Productos locales de Lanzarote</div>
            </div>
            <div class="invoice-meta">
                <h1>FACTURA</h1>
                <div class="num">#{{ str_pad($pedido->id, 6, '0', STR_PAD_LEFT) }}</div>
                <div class="badge-estado">{{ strtoupper($pedido->estado) }}</div>
            </div>
        </div>

        <!-- Info -->
        <div class="info-grid">
            <div class="info-box">
                <div class="info-label">Facturado a</div>
                <strong>{{ $pedido->user?->name ?? 'Cliente' }}</strong>
                <span>{{ $pedido->user?->email ?? '' }}</span>
                @if($pedido->direccion_envio)
                    <span style="margin-top:6px;">{{ $pedido->direccion_envio }}</span>
                @endif
            </div>
            <div class="info-box">
                <div class="info-label">Detalles del pedido</div>
                <strong>Pedido #{{ str_pad($pedido->id, 6, '0', STR_PAD_LEFT) }}</strong>
                <span>Fecha: {{ $pedido->created_at->format('d/m/Y H:i') }}</span>
                @if($pedido->updated_at && $pedido->estado === 'entregado')
                    <span>Entregado: {{ $pedido->updated_at->format('d/m/Y') }}</span>
                @endif
                <span style="margin-top:6px;">Método de pago: Tarjeta / Online</span>
            </div>
        </div>

        <!-- Items -->
        <div class="items-section">
            <div class="section-title">Artículos del pedido</div>
            <table>
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Precio unit.</th>
                        <th>Cant.</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pedido->items as $item)
                    <tr>
                        <td>
                            <div class="item-name">{{ $item->producto_nombre ?? $item->producto?->nombre ?? '–' }}</div>
                            @if($item->tienda_nombre)
                                <div class="item-store">{{ $item->tienda_nombre }}</div>
                            @endif
                        </td>
                        <td>{{ number_format((float)$item->precio_unitario, 2, ',', '.') }} €</td>
                        <td>{{ $item->cantidad }}</td>
                        <td><strong>{{ number_format((float)$item->subtotal, 2, ',', '.') }} €</strong></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Totales -->
        <div class="totals">
            <div class="totals-inner">
                <div class="totals-row">
                    <span>Subtotal</span>
                    <span>{{ number_format((float)$pedido->total, 2, ',', '.') }} €</span>
                </div>
                <div class="totals-row">
                    <span>Envío</span>
                    <span>Gratis</span>
                </div>
                <div class="totals-row">
                    <span>IVA (21%)</span>
                    <span>Incluido</span>
                </div>
                <div class="totals-row grand">
                    <span>Total</span>
                    <span>{{ number_format((float)$pedido->total, 2, ',', '.') }} €</span>
                </div>
            </div>
        </div>

        <!-- Pie -->
        <div class="invoice-footer">
            <div>
                <div class="footer-brand">Rustikan</div>
                <div>Lanzarote, Islas Canarias — rustikan.local</div>
            </div>
            <div style="text-align:right;">
                <div>NIF: B-XXXXXXXX</div>
                <div>Generado el {{ now()->format('d/m/Y') }}</div>
            </div>
        </div>
    </div>
</body>
</html>
