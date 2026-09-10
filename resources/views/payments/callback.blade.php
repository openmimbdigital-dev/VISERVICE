<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @if($retry_url ?? null)
    {{-- PSE puede tardar: se reintenta la consulta unas cuantas veces y luego se deja de insistir. --}}
    <meta http-equiv="refresh" content="6;url={{ $retry_url }}">
    @endif
    <title>{{ ($state ?? '') === 'pagado' ? 'Pago confirmado' : 'Pago recibido' }} — SouulBi</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/brand/icon.svg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-slate-100">
    <div class="flex min-h-screen items-center justify-center p-4">
        <div class="w-full max-w-md overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
            <div class="relative overflow-hidden bg-gradient-to-br from-slate-900 via-indigo-950 to-slate-900 px-6 py-8 text-center">
                <div class="pointer-events-none absolute -right-16 -top-20 h-48 w-48 rounded-full bg-indigo-500/20 blur-3xl" aria-hidden="true"></div>

                @if($state === 'pagado')
                <span class="relative mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-emerald-500/15 ring-2 ring-emerald-400/30">
                    <svg class="h-7 w-7 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </span>

                <h1 class="relative mt-4 text-xl font-bold text-white">Pago confirmado</h1>
                <p class="relative mt-2 text-sm text-slate-300">Tu suscripción ya está activa.</p>
                @else
                <span class="relative mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-indigo-500/15 ring-2 ring-indigo-400/30">
                    <svg class="h-7 w-7 text-indigo-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l2.5 2.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </span>

                <h1 class="relative mt-4 text-xl font-bold text-white">Gracias por tu pago</h1>
                <p class="relative mt-2 text-sm text-slate-300">Estamos confirmando la transacción con la pasarela.</p>
                @endif
            </div>

            <div class="space-y-4 px-6 py-6">
                @if($invoice)
                <div class="flex items-baseline justify-between gap-3 rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-3">
                    <div class="min-w-0">
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Cobro</p>
                        <p class="mt-0.5 font-mono text-xs text-slate-700">{{ $invoice->invoice_number }}</p>
                    </div>
                    <p class="shrink-0 text-lg font-bold text-slate-900">{{ col_money($invoice->amount) }}</p>
                </div>
                @endif

                @if($state === 'pagado')
                <p class="text-sm leading-relaxed text-slate-600">
                    Ya registramos el pago y generamos tu factura. Puedes entrar a la plataforma
                    y seguir trabajando con normalidad.
                </p>
                @elseif($state === 'en_proceso')
                <p class="text-sm leading-relaxed text-slate-600">
                    La pasarela todavía está procesando la transacción. Esta página se actualiza
                    sola en unos segundos; no hace falta que pagues de nuevo.
                </p>
                @else
                <p class="text-sm leading-relaxed text-slate-600">
                    La confirmación puede tardar unos instantes. Cuando el pago quede acreditado,
                    tu suscripción se activa automáticamente y recibirás la factura.
                </p>
                @endif

                @if($state !== 'pagado')
                <p class="rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-3 text-xs text-slate-500">
                    Puedes cerrar esta ventana y volver a la plataforma: el estado se actualiza solo.
                </p>
                @endif

                <a href="{{ route('login') }}"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700">
                    Ir a la plataforma
                </a>
            </div>
        </div>
    </div>
</body>
</html>
