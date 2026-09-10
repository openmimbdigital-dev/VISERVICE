<div class="relative mx-auto w-full max-w-[90rem] p-4 sm:p-6">
    <div class="pointer-events-none absolute -top-4 left-1/2 h-px w-[min(100%,48rem)] -translate-x-1/2 bg-gradient-to-r from-transparent via-indigo-300/40 to-transparent" aria-hidden="true"></div>

    <nav class="mb-6 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs font-medium text-slate-500">
        <a href="{{ route('admin.presentation.dashboard') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Inicio</a>
        <span class="text-slate-300">/</span>
        <a href="{{ route('admin.presentation.enrollment.index') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Inscripción</a>
        <span class="text-slate-300">/</span>
        <span class="font-semibold text-slate-900">Nueva inscripción</span>
    </nav>

    <header class="mb-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0 flex-1 border-l-4 border-indigo-600 pl-4 sm:pl-5">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-indigo-600/90">Inscripción</p>
                <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900">Inscribir estudiante</h1>
                <p class="mt-2 max-w-xl text-sm text-slate-600">El acudiente registra al hijo, carga la documentación y hace el pago. No se guarda en base de datos.</p>
            </div>
            <a href="{{ route('admin.presentation.enrollment.index') }}" wire:navigate class="btn btn-outline-secondary btn-sm w-full justify-center sm:w-auto">Volver</a>
        </div>
    </header>

    <ol class="mb-6 grid grid-cols-2 gap-2 sm:grid-cols-4">
        @foreach([1 => 'Acudiente', 2 => 'Estudiante', 3 => 'Documentos', 4 => 'Pago'] as $number => $label)
        <li class="rounded-xl border px-3 py-2 text-sm {{ $step === $number ? 'border-indigo-200 bg-indigo-50 font-semibold text-indigo-700' : ($step > $number ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-slate-200 bg-white text-slate-500') }}">
            <span class="tabular-nums">{{ $number }}.</span> {{ $label }}
        </li>
        @endforeach
    </ol>

    <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
        <form wire:submit="{{ $step === 4 ? 'save' : 'nextStep' }}" class="flex min-h-0 flex-1 flex-col">
            <div class="flex-1 space-y-4 px-4 py-5 sm:px-6">
                @if($step === 1)
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="mb-1.5 block text-xs font-medium text-slate-700">Nombre del acudiente <span class="text-rose-500">*</span></label>
                        <input type="text" wire:model="parent_name" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 @error('parent_name') border-rose-400 bg-rose-50 @enderror">
                        @error('parent_name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-slate-700">Documento <span class="text-rose-500">*</span></label>
                        <input type="text" wire:model="parent_document" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 @error('parent_document') border-rose-400 bg-rose-50 @enderror">
                        @error('parent_document') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-slate-700">Teléfono <span class="text-rose-500">*</span></label>
                        <input type="text" wire:model="parent_phone" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 @error('parent_phone') border-rose-400 bg-rose-50 @enderror">
                        @error('parent_phone') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-1.5 block text-xs font-medium text-slate-700">Correo <span class="text-rose-500">*</span></label>
                        <input type="email" wire:model="parent_email" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 @error('parent_email') border-rose-400 bg-rose-50 @enderror">
                        @error('parent_email') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                </div>
                @endif

                @if($step === 2)
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="mb-1.5 block text-xs font-medium text-slate-700">Nombre del estudiante <span class="text-rose-500">*</span></label>
                        <input type="text" wire:model="child_name" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 @error('child_name') border-rose-400 bg-rose-50 @enderror">
                        @error('child_name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-slate-700">Documento <span class="text-rose-500">*</span></label>
                        <input type="text" wire:model="child_document" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 @error('child_document') border-rose-400 bg-rose-50 @enderror">
                        @error('child_document') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-slate-700">Grado <span class="text-rose-500">*</span></label>
                        <select wire:model="grade" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                            @foreach($grades as $option)
                            <option value="{{ $option }}">{{ $option }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-1.5 block text-xs font-medium text-slate-700">Jornada <span class="text-rose-500">*</span></label>
                        <select wire:model="shift" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                            @foreach($shifts as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                @endif

                @if($step === 3)
                <p class="text-sm text-slate-600">Carga la documentación del estudiante. En esta demostración el archivo no se guarda en disco, solo se registra el nombre.</p>
                <div class="grid grid-cols-1 gap-4">
                    @foreach($document_types as $type => $label)
                    @php $field = 'doc_'.$type; @endphp
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-slate-700">{{ $label }}</label>
                        <input type="file" wire:model="{{ $field }}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-indigo-700">
                        @if($this->{$field})
                        <p class="mt-1 text-xs text-emerald-600">Listo: {{ $this->{$field}->getClientOriginalName() }}</p>
                        @endif
                        @error($field) <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    @endforeach
                </div>
                @endif

                @if($step === 4)
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-slate-700">Valor a pagar <span class="text-rose-500">*</span></label>
                        <input type="number" min="1" wire:model="payment_amount" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 @error('payment_amount') border-rose-400 bg-rose-50 @enderror">
                        @error('payment_amount') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-slate-700">Medio de pago <span class="text-rose-500">*</span></label>
                        <select wire:model="payment_method" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                            @foreach($payment_methods as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-1.5 block text-xs font-medium text-slate-700">Referencia de pago</label>
                        <input type="text" wire:model="payment_reference" placeholder="Ej. TRX-12345" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                    </div>
                </div>
                @endif
            </div>
            <div class="flex shrink-0 flex-col-reverse gap-2 border-t border-slate-100 px-4 py-4 sm:flex-row sm:justify-end sm:gap-3 sm:px-6">
                @if($step > 1)
                <button type="button" wire:click="previousStep" class="w-full rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-200 sm:w-auto">Atrás</button>
                @endif
                <button type="submit" wire:loading.attr="disabled" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700 disabled:opacity-60 sm:w-auto">
                    {{ $step === 4 ? 'Finalizar inscripción' : 'Continuar' }}
                </button>
            </div>
        </form>
    </section>
</div>
