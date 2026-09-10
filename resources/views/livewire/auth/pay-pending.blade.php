<div>
    @if($invoice)
    <div class="rounded-2xl border border-white/10 bg-white/5 px-5 py-4 text-left backdrop-blur-sm">
        <div class="flex items-baseline justify-between gap-3">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Cobro pendiente</p>
            <p class="font-mono text-[11px] text-slate-400">{{ $invoice->invoice_number }}</p>
        </div>

        <p class="mt-1 text-2xl font-bold text-white">{{ col_money($invoice->amount) }}</p>

        @if($invoice->subscription?->plan)
        <p class="mt-0.5 text-xs text-slate-400">{{ $invoice->subscription->plan->name }}</p>
        @endif

        @if($bold_enabled)
        <button type="button" wire:click="payOnline" wire:loading.attr="disabled" wire:target="payOnline"
            class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-indigo-700 disabled:opacity-60">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" wire:loading.remove wire:target="payOnline">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
            </svg>
            <span wire:loading.remove wire:target="payOnline">Pagar en línea ahora</span>
            <span wire:loading wire:target="payOnline">Abriendo la pasarela…</span>
        </button>

        <p class="mt-2 text-center text-[11px] text-slate-400">
            Tarjeta, PSE, Nequi o botón Bancolombia. Tu cuenta se activa apenas se apruebe el pago.
        </p>
        @endif

        @if($error)
        <p class="mt-3 rounded-lg border border-rose-400/30 bg-rose-500/10 px-3 py-2 text-xs text-rose-200">
            {{ $error }}
        </p>
        @endif
    </div>
    @endif
</div>
