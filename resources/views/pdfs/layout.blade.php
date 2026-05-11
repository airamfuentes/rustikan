<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>@yield('titulo', 'Informe') — Rustikan</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            font-size: 12.5px;
            color: #1f2937;
            background: #f3f4f6;
            padding: 24px;
        }
        .sheet {
            max-width: 880px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 4px 32px rgba(0,0,0,.10);
            overflow: hidden;
        }
        .pdf-header {
            background: linear-gradient(135deg, #ea580c 0%, #f97316 60%, #fb923c 100%);
            color: white;
            padding: 32px 40px 26px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        .pdf-brand-name {
            font-size: 26px;
            font-weight: 800;
            letter-spacing: -0.5px;
        }
        .pdf-brand-tagline {
            font-size: 11px;
            opacity: .85;
            margin-top: 4px;
            font-style: italic;
        }
        .pdf-meta { text-align: right; font-size: 12px; }
        .pdf-meta .meta-line { margin-top: 2px; opacity: .9; }
        .pdf-meta .meta-title {
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 6px;
            letter-spacing: .3px;
        }

        /* Cuerpo */
        .pdf-body { padding: 28px 40px 36px; }
        .pdf-title {
            font-size: 18px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 4px;
        }
        .pdf-subtitle {
            font-size: 13px;
            color: #6b7280;
            margin-bottom: 22px;
        }

        /* Tarjetas resumen */
        .summary-grid {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 24px;
        }
        .summary-card {
            flex: 1;
            min-width: 150px;
            background: #fff7ed;
            border: 1px solid #fed7aa;
            border-radius: 10px;
            padding: 14px 16px;
        }
        .summary-card .label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .8px;
            color: #9a3412;
            font-weight: 600;
        }
        .summary-card .value {
            font-size: 20px;
            font-weight: 800;
            color: #c2410c;
            margin-top: 4px;
        }

        /* Tablas */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }
        thead th {
            background: #f9fafb;
            color: #374151;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .5px;
            font-size: 10.5px;
            padding: 10px 12px;
            text-align: left;
            border-bottom: 2px solid #e5e7eb;
        }
        tbody td {
            padding: 10px 12px;
            border-bottom: 1px solid #f3f4f6;
            color: #1f2937;
        }
        tbody tr:nth-child(even) td { background: #fafafa; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .num { font-variant-numeric: tabular-nums; }

        tfoot td {
            padding: 12px;
            font-weight: 800;
            border-top: 2px solid #fb923c;
            background: #fffbeb;
            color: #92400e;
        }

        /* Estados */
        .badge {
            display: inline-block;
            padding: 2px 8px;
            font-size: 10.5px;
            font-weight: 700;
            border-radius: 9999px;
            text-transform: uppercase;
            letter-spacing: .3px;
        }
        .badge-green   { background: #dcfce7; color: #166534; }
        .badge-blue    { background: #dbeafe; color: #1e40af; }
        .badge-yellow  { background: #fef9c3; color: #854d0e; }
        .badge-orange  { background: #ffedd5; color: #9a3412; }
        .badge-red     { background: #fee2e2; color: #991b1b; }
        .badge-gray    { background: #f3f4f6; color: #374151; }

        /* Footer */
        .pdf-footer {
            margin-top: 24px;
            padding: 16px 40px;
            background: #f9fafb;
            border-top: 1px solid #e5e7eb;
            font-size: 11px;
            color: #6b7280;
            display: flex;
            justify-content: space-between;
        }
        .pdf-footer a { color: #ea580c; text-decoration: none; }

        /* Barra de acciones (no se imprime) */
        .actions-bar {
            max-width: 880px;
            margin: 0 auto 12px;
            display: flex;
            justify-content: flex-end;
            gap: 8px;
        }
        .btn {
            background: #f97316;
            color: white;
            border: 0;
            padding: 8px 18px;
            font-size: 13px;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            font-family: inherit;
        }
        .btn:hover { background: #ea580c; }
        .btn-ghost { background: #fff; color: #6b7280; border: 1px solid #e5e7eb; }
        .btn-ghost:hover { background: #f9fafb; }

        /* Impresión / PDF */
        @media print {
            body { background: #fff; padding: 0; font-size: 11.5px; }
            .sheet { box-shadow: none; border-radius: 0; }
            .actions-bar { display: none !important; }
            .pdf-footer { background: transparent; border-top: 1px solid #d1d5db; }
            @page { size: A4; margin: 14mm 12mm; }
        }
    </style>
</head>
<body>
    <div class="actions-bar">
        <button class="btn-ghost btn" onclick="window.history.back()">← Volver</button>
        <button class="btn" onclick="window.print()">Guardar como PDF</button>
    </div>

    <div class="sheet">
        <header class="pdf-header">
            <div>
                <div class="pdf-brand-name">Rustikan</div>
                <div class="pdf-brand-tagline">Productos locales de Lanzarote</div>
            </div>
            <div class="pdf-meta">
                <div class="meta-title">@yield('titulo', 'Informe')</div>
                <div class="meta-line">Generado: {{ now()->format('d/m/Y H:i') }}</div>
                @hasSection('meta')
                    @yield('meta')
                @endif
            </div>
        </header>

        <div class="pdf-body">
            @yield('content')
        </div>

        <footer class="pdf-footer">
            <span>Rustikan — Lanzarote, Islas Canarias</span>
            <span><a href="mailto:info@rustikan.com">info@rustikan.com</a></span>
        </footer>
    </div>
</body>
</html>
