<div class="relative mx-auto w-full max-w-[90rem] p-4 sm:p-6">
    <div class="pointer-events-none absolute -top-4 left-1/2 h-px w-[min(100%,48rem)] -translate-x-1/2 bg-gradient-to-r from-transparent via-indigo-300/40 to-transparent" aria-hidden="true"></div>

    <nav class="mb-6 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs font-medium text-slate-500">
        <a href="{{ route('dashboard') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Inicio</a>
        <span class="text-slate-300">/</span>
        <a href="{{ route('admin.workshop.advance-payments.index') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Gestión de anticipo</a>
        <span class="text-slate-300">/</span>
        <span class="font-semibold text-slate-900">{{ $workOrder->reference }}</span>
    </nav>

    <header class="mb-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0 flex-1 border-l-4 border-indigo-600 pl-4 sm:pl-5">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-indigo-600/90">Anticipo</p>
                <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900">{{ $workOrder->reference }}</h1>
                <p class="mt-2 text-sm text-slate-600">
                    {{ $workOrder->client?->name ?? '—' }}
                    @if($workOrder->equipment)
                    · {{ $workOrder->equipment->select_label ?? $workOrder->equipment->plate }}
                    @endif
                </p>
                <p class="mt-2 max-w-xl text-xs text-slate-500">
                    El anticipo definido al crear la OT aparece como «Definido». Aquí registras abonos parciales o totales hasta cubrirlo.
                </p>
            </div>
            <div class="flex w-full shrink-0 flex-wrap gap-2 sm:w-auto">
                <a href="{{ route('admin.workshop.advance-payments.index') }}" wire:navigate
                    class="btn btn-outline-secondary btn-sm flex-1 justify-center sm:flex-none">Volver</a>
                @can('workshop.work-orders.view')
                <a href="{{ route('admin.workshop.work-orders.show', $workOrder) }}" wire:navigate
                    class="btn btn-outline-secondary btn-sm flex-1 justify-center sm:flex-none">Ver OT</a>
                @endcan
                @can('workshop.advance-payments.pay')
                @if($advance_remaining > 0)
                <x-ui.create-button wire:click="openPaymentModal" size="sm" class="flex-1 justify-center sm:flex-none">
                    Registrar abono
                </x-ui.create-button>
                @else
                <button type="button" disabled title="Anticipo cubierto. Anula un abono para liberar saldo."
                    class="inline-flex flex-1 cursor-not-allowed items-center justify-center gap-2 rounded-xl bg-indigo-600 px-3.5 py-2 text-sm font-semibold text-white opacity-50 sm:flex-none">
                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Registrar abono
                </button>
                @endif
                @endcan
            </div>
        </div>
    </header>

    <div class="mb-6 grid grid-cols-1 gap-3 sm:grid-cols-2">
        <div class="rounded-2xl border border-amber-100 bg-amber-50/80 p-4 shadow-sm">
            <p class="text-[10px] font-semibold uppercase tracking-wider text-amber-700/80">Acordado ({{ rtrim(rtrim(number_format((float) $workOrder->advance_percentage, 2, '.', ''), '0'), '.') }}%)</p>
            <p class="mt-1 text-2xl font-semibold tabular-nums text-amber-900">{{ col_money($advance_agreed) }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200/90 bg-white p-4 shadow-sm ring-1 ring-slate-900/[0.035]">
            <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Pendiente</p>
            <p class="mt-1 text-2xl font-semibold tabular-nums text-slate-900">{{ col_money($advance_remaining) }}</p>
            @if($advance_remaining <= 0)
            <p class="mt-1 text-xs text-emerald-700">Anticipo cubierto. Anula un abono si necesitas registrar otro.</p>
            @endif
        </div>
    </div>

    <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
        <div class="flex flex-col gap-3 border-b border-slate-100 bg-slate-50/80 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-5">
            <div>
                <h2 class="font-semibold text-slate-800">Seguimiento del anticipo</h2>
                <p class="mt-1 text-xs text-slate-500">Registro definido + abonos cobrados. Solo los abonos confirmados reducen el pendiente.</p>
            </div>
            @can('workshop.advance-payments.pay')
            @if($advance_remaining > 0)
            <x-ui.create-button wire:click="openPaymentModal" size="sm" class="w-full justify-center sm:w-auto">
                Registrar abono
            </x-ui.create-button>
            @endif
            @endcan
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100">
                <thead class="bg-slate-50/40">
                    <tr>
                        <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500 sm:px-4">Fecha</th>
                        <th class="px-3 py-2 text-right text-xs font-semibold uppercase text-slate-500 sm:px-4">Monto</th>
                        <th class="hidden px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500 sm:table-cell sm:px-4">Método</th>
                        <th class="hidden px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500 md:table-cell sm:px-4">Referencia</th>
                        <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500 sm:px-4">Estado</th>
                        <th class="px-3 py-2 text-right text-xs font-semibold uppercase text-slate-500 sm:px-4">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($workOrder->payments as $payment)
                    <tr wire:key="payment-{{ $payment->id }}" @class(['bg-amber-50/40' => $payment->status === 'pending'])>
                        <td class="px-3 py-3 text-sm text-slate-600 sm:px-4">
                            @if($payment->status === 'pending')
                                <span class="text-slate-400">—</span>
                            @else
                                {{ $payment->paid_at?->format('d/m/Y H:i') ?? '—' }}
                            @endif
                        </td>
                        <td class="px-3 py-3 text-right text-sm font-semibold tabular-nums sm:px-4 @if($payment->status === 'pending') text-amber-800 @else text-amber-700 @endif">
                            {{ col_money($payment->amount) }}
                        </td>
                        <td class="hidden px-3 py-3 text-sm text-slate-600 sm:table-cell sm:px-4">
                            @if($payment->status === 'pending')
                                <span class="text-slate-400">Anticipo definido</span>
                            @else
                                {{ $payment->paymentMethod?->name ?? $payment->payment_method ?? '—' }}
                            @endif
                        </td>
                        <td class="hidden px-3 py-3 text-sm text-slate-500 md:table-cell sm:px-4">
                            {{ $payment->payment_reference ?: ($payment->notes ? \Illuminate\Support\Str::limit($payment->notes, 40) : '—') }}
                        </td>
                        <td class="px-3 py-3 sm:px-4">
                            @php
                                $badge = match ($payment->status) {
                                    'pending' => 'bg-amber-100 text-amber-800',
                                    'confirmed' => 'bg-emerald-100 text-emerald-800',
                                    'voided' => 'bg-rose-100 text-rose-800',
                                    default => 'bg-slate-100 text-slate-700',
                                };
                            @endphp
                            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium {{ $badge }}">
                                {{ $payment->status_label }}
                            </span>
                        </td>
                        <td class="px-3 py-3 text-right sm:px-4">
                            @if($can_void && $payment->status === 'confirmed')
                            <button type="button" wire:click="voidPayment({{ $payment->id }})" title="Anular abono"
                                class="inline-flex items-center gap-1 rounded-lg px-2 py-1.5 text-xs font-medium text-rose-600 transition hover:bg-rose-50">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                                <span class="hidden sm:inline">Anular</span>
                            </button>
                            @elseif($payment->status === 'pending')
                            <span class="text-xs text-slate-400">Seguimiento</span>
                            @else
                            <span class="text-slate-300">—</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-10 text-center text-sm text-slate-400">
                            Sin registros de anticipo.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    @if($showPaymentModal)
    <x-ui.modal centered maxWidth="lg">
        <x-slot:backdrop>
            <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" wire:click="closePaymentModal"></div>
        </x-slot:backdrop>

        <div class="flex shrink-0 items-center justify-between border-b border-slate-100 px-4 py-4 sm:px-6">
            <h3 class="text-base font-semibold text-slate-900">Registrar abono</h3>
            <button type="button" wire:click="closePaymentModal" class="rounded-lg p-1 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form wire:submit="savePayment" class="flex min-h-0 flex-1 flex-col">
            <div class="flex-1 space-y-4 overflow-y-auto px-4 py-5 sm:px-6">
                <p class="rounded-xl border border-amber-100 bg-amber-50 px-3.5 py-2.5 text-xs text-amber-800">
                    Saldo pendiente: <strong>{{ col_money($advance_remaining) }}</strong>
                </p>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div
                        x-data="{
                            display: @js($amount !== '' ? col_number((float) $amount) : ''),
                            apply(value, caret) {
                                const before = String(value).slice(0, caret ?? String(value).length);
                                const sigBefore = before.replace(/[^\d,]/g, '').length;

                                let cleaned = String(value).replace(/[^\d,]/g, '');
                                const commaIndex = cleaned.indexOf(',');
                                if (commaIndex !== -1) {
                                    cleaned = cleaned.slice(0, commaIndex + 1) + cleaned.slice(commaIndex + 1).replace(/,/g, '');
                                }

                                let intPart = cleaned;
                                let decPart = '';
                                let hasComma = false;
                                if (cleaned.includes(',')) {
                                    hasComma = true;
                                    const parts = cleaned.split(',');
                                    intPart = parts[0];
                                    decPart = (parts[1] || '').slice(0, 2);
                                }

                                intPart = intPart.replace(/^0+(?=\d)/, '');
                                const formattedInt = intPart.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                                this.display = hasComma ? `${formattedInt},${decPart}` : formattedInt;

                                const raw = intPart === '' && decPart === ''
                                    ? ''
                                    : (hasComma && decPart !== '' ? `${intPart || '0'}.${decPart}` : (intPart || '0'));
                                $wire.set('amount', raw, false);

                                this.$nextTick(() => {
                                    const el = this.$refs.amountInput;
                                    if (! el) return;
                                    let seen = 0;
                                    let pos = this.display.length;
                                    for (let i = 0; i < this.display.length; i++) {
                                        if (/[\d,]/.test(this.display[i])) {
                                            seen++;
                                            if (seen >= sigBefore) {
                                                pos = i + 1;
                                                break;
                                            }
                                        }
                                    }
                                    el.setSelectionRange(pos, pos);
                                });
                            }
                        }"
                    >
                        <label class="mb-1.5 block text-xs font-medium text-slate-700">Monto <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-3.5 flex items-center text-sm font-medium text-slate-400">$</span>
                            <input type="text"
                                x-ref="amountInput"
                                inputmode="decimal"
                                autocomplete="off"
                                :value="display"
                                @input="apply($event.target.value, $event.target.selectionStart)"
                                placeholder="0,00"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-8 pr-3.5 text-right text-sm tabular-nums transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 @error('amount') border-rose-400 bg-rose-50 @enderror">
                        </div>
                        @error('amount') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-slate-700">Fecha de pago <span class="text-rose-500">*</span></label>
                        <input type="datetime-local" wire:model="paid_at"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm @error('paid_at') border-rose-400 bg-rose-50 @enderror">
                        @error('paid_at') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-slate-700">Método de pago</label>
                        <select wire:model="business_payment_method_id"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm">
                            <option value="">—</option>
                            @foreach($payment_methods as $method)
                                <option value="{{ $method->id }}">{{ $method->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-slate-700">Cuenta bancaria</label>
                        <select wire:model="business_bank_account_id"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm">
                            <option value="">—</option>
                            @foreach($bank_accounts as $account)
                                <option value="{{ $account->id }}">{{ $account->bank_name }} — {{ $account->account_number }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-1.5 block text-xs font-medium text-slate-700">Referencia</label>
                        <input type="text" wire:model="payment_reference" maxlength="120"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-1.5 block text-xs font-medium text-slate-700">Notas</label>
                        <textarea wire:model="notes" rows="2"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm"></textarea>
                    </div>
                </div>
            </div>

            <div class="flex shrink-0 flex-col-reverse gap-2 border-t border-slate-100 px-4 py-4 sm:flex-row sm:justify-end sm:gap-3 sm:px-6">
                <button type="button" wire:click="closePaymentModal" class="w-full rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-200 sm:w-auto">Cancelar</button>
                <button type="submit" wire:loading.attr="disabled" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700 disabled:opacity-60 sm:w-auto">
                    <span wire:loading.remove wire:target="savePayment">Guardar abono</span>
                    <span wire:loading wire:target="savePayment">Guardando…</span>
                </button>
            </div>
        </form>
    </x-ui.modal>
    @endif
</div>
