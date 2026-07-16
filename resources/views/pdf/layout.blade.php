<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'Documento' }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1e293b; margin: 24px; }
        h1 { font-size: 16px; margin: 0 0 4px; text-transform: uppercase; }
        h2 { font-size: 13px; margin: 0; color: #4338ca; }
        .muted { color: #64748b; font-size: 10px; }
        .header { width: 100%; border-bottom: 2px solid #1e293b; padding-bottom: 12px; margin-bottom: 14px; }
        .header td { vertical-align: top; }
        .header-right { text-align: right; }
        .ref { font-family: DejaVu Sans Mono, monospace; font-size: 13px; font-weight: bold; }
        .section { margin-top: 12px; }
        .section-title { font-size: 10px; font-weight: bold; text-transform: uppercase; color: #64748b; margin-bottom: 4px; }
        .grid { width: 100%; }
        .grid td { width: 50%; vertical-align: top; padding-right: 12px; }
        table.items { width: 100%; border-collapse: collapse; margin-top: 14px; }
        table.items th { background: #f8fafc; border-bottom: 1px solid #cbd5e1; text-align: left; padding: 5px 4px; font-size: 10px; }
        table.items td { border-bottom: 1px solid #f1f5f9; padding: 5px 4px; vertical-align: top; }
        .text-right { text-align: right; }
        .totals { width: 280px; margin-left: auto; margin-top: 12px; }
        .totals td { padding: 3px 0; }
        .totals .total td { font-size: 13px; font-weight: bold; color: #4338ca; border-top: 1px solid #cbd5e1; padding-top: 6px; }
        .box { border: 1px solid #e2e8f0; padding: 8px; margin-top: 12px; }
        .signatures { width: 100%; margin-top: 40px; }
        .signatures td { width: 50%; vertical-align: top; padding-right: 20px; }
        .sig-line { border-top: 1px solid #94a3b8; margin-top: 36px; padding-top: 4px; }
        .bold { font-weight: bold; }
    </style>
</head>
<body>
    @yield('content')
</body>
</html>
