<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Documento' }} — SouulBi</title>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white; }
        }
    </style>
    @vite(['resources/css/app.css'])
    @livewireStyles
</head>
<body class="bg-white text-slate-900 antialiased">
    <div class="no-print fixed right-4 top-4 z-50 flex gap-2">
        <button type="button" onclick="window.print()" class="btn btn-primary btn-sm">Imprimir</button>
        @if(! empty($pdfUrl))
        <a href="{{ $pdfUrl }}" class="btn btn-outline-secondary btn-sm">Exportar PDF</a>
        @endif
        <a href="{{ $backUrl ?? url()->previous() }}" class="btn btn-outline-secondary btn-sm">Volver</a>
    </div>
    {{ $slot }}
    @livewireScripts
</body>
</html>
