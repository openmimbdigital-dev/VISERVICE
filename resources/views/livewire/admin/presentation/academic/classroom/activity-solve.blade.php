@php
    $type = $activity_record['type'];
    $submitted = $activity_record['submitted'];
    $type_hint = match ($type) {
        'evaluation' => 'Responde cada pregunta. Al enviar se califica automáticamente.',
        'homework' => 'Desarrolla la tarea y, si quieres, adjunta el nombre del archivo.',
        default => 'Completa los ejercicios cortos de la clase y envía tus respuestas.',
    };
@endphp

<div class="relative mx-auto w-full max-w-[90rem] p-4 sm:p-6">
    <div class="pointer-events-none absolute -top-4 left-1/2 h-px w-[min(100%,48rem)] -translate-x-1/2 bg-gradient-to-r from-transparent via-indigo-300/40 to-transparent" aria-hidden="true"></div>

    <nav class="mb-6 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs font-medium text-slate-500">
        <a href="{{ route('admin.presentation.dashboard') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Inicio</a>
        <span class="text-slate-300">/</span>
        <a href="{{ route('admin.presentation.academic.classroom.show', $subject['id']) }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">{{ $subject['name'] }}</a>
        <span class="text-slate-300">/</span>
        <a href="{{ route('admin.presentation.academic.classroom.activities.index', [$subject['id'], $activity_type]) }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">{{ $activity_record['type_label'] }}</a>
        <span class="text-slate-300">/</span>
        <span class="font-semibold text-slate-900">Resolver</span>
    </nav>

    <header class="mb-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0 flex-1 border-l-4 border-indigo-600 pl-4 sm:pl-5">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-indigo-600/90">{{ $activity_record['type_label'] }}</p>
                <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900">{{ $activity_record['title'] }}</h1>
                <p class="mt-2 max-w-xl text-sm text-slate-600">{{ $type_hint }}</p>
            </div>
            <div class="flex w-full shrink-0 flex-col gap-2 sm:w-auto sm:flex-row">
                <a href="{{ route('admin.presentation.academic.classroom.activities.index', [$subject['id'], $activity_type]) }}" wire:navigate class="btn btn-outline-secondary btn-sm flex-1 justify-center sm:flex-none">Volver</a>
                @if($submitted)
                <button type="button" wire:click="retry" class="btn btn-outline-secondary btn-sm flex-1 justify-center sm:flex-none">Resolver de nuevo</button>
                @endif
            </div>
        </div>
    </header>

    <div class="mb-6 grid grid-cols-1 gap-3 sm:grid-cols-3">
        <div class="rounded-2xl border border-slate-200/90 bg-white p-4 shadow-sm ring-1 ring-slate-900/[0.04]">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Entrega</p>
            <p class="mt-2 text-sm font-medium text-slate-900">{{ \Carbon\Carbon::parse($activity_record['due_date'])->format('d/m/Y') }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200/90 bg-white p-4 shadow-sm ring-1 ring-slate-900/[0.04]">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Nota máxima</p>
            <p class="mt-2 text-2xl font-semibold tabular-nums text-slate-900">{{ number_format((float) $activity_record['max_score'], 1) }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200/90 bg-white p-4 shadow-sm ring-1 ring-slate-900/[0.04]">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Estado</p>
            @if($submitted)
                @if(($submission['status'] ?? '') === 'pending_review')
                <p class="mt-2 text-sm font-medium text-amber-700">Entregada · pendiente de calificación</p>
                @else
                <p class="mt-2 text-2xl font-semibold tabular-nums text-indigo-600">{{ number_format((float) ($submission['score'] ?? 0), 1) }}</p>
                @endif
            @else
            <p class="mt-2 text-sm font-medium text-slate-700">Sin enviar</p>
            @endif
        </div>
    </div>

    @if($activity_record['description'] !== '' && $activity_record['description'] !== 'Sin descripción')
    <p class="mb-6 rounded-2xl border border-slate-200/90 bg-slate-50 px-4 py-3 text-sm text-slate-700">{{ $activity_record['description'] }}</p>
    @endif

    <form wire:submit="save">
        <div class="space-y-4">
            @foreach($activity_record['items'] as $index => $item)
            <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
                <div class="border-b border-slate-100 bg-slate-50/80 px-4 py-4 sm:px-5">
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-indigo-600">
                        @if($type === 'evaluation') Pregunta {{ $index + 1 }}
                        @elseif($type === 'homework') Consigna
                        @else Ejercicio {{ $index + 1 }}
                        @endif
                    </p>
                    <h2 class="mt-1 text-sm font-semibold text-slate-900 sm:text-base">{{ $item['prompt'] }}</h2>
                </div>
                <div class="space-y-3 px-4 py-5 sm:px-5">
                    @if($item['kind'] === 'choice')
                        @foreach($item['options'] as $value => $label)
                        @php
                            $selected = ($answers[$item['id']] ?? '') === $value;
                            $show_result = $submitted && $item['kind'] === 'choice';
                            $is_correct = ($item['correct'] ?? '') === $value;
                        @endphp
                        <label @class([
                            'flex cursor-pointer items-start gap-3 rounded-xl border px-3.5 py-3 text-sm transition',
                            'border-emerald-300 bg-emerald-50' => $show_result && $is_correct,
                            'border-rose-300 bg-rose-50' => $show_result && $selected && ! $is_correct,
                            'border-slate-200 bg-slate-50 hover:border-indigo-200' => ! $show_result,
                        ])>
                            <input type="radio" wire:model="answers.{{ $item['id'] }}" value="{{ $value }}" @disabled($submitted)
                                class="mt-0.5 border-slate-300 text-indigo-600 focus:ring-indigo-500 disabled:opacity-60">
                            <span class="text-slate-800">{{ strtoupper($value) }}. {{ $label }}</span>
                        </label>
                        @endforeach
                    @elseif($item['kind'] === 'essay')
                        <textarea wire:model="answers.{{ $item['id'] }}" rows="8" @disabled($submitted)
                            placeholder="Escribe tu desarrollo aquí..."
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 disabled:bg-slate-100 @error('answers.'.$item['id']) border-rose-400 bg-rose-50 @enderror"></textarea>
                    @else
                        <input type="text" wire:model="answers.{{ $item['id'] }}" @disabled($submitted)
                            placeholder="Tu respuesta"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 disabled:bg-slate-100 @error('answers.'.$item['id']) border-rose-400 bg-rose-50 @enderror">
                    @endif
                    @error('answers.'.$item['id']) <p class="text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
            </section>
            @endforeach

            @if($type === 'homework')
            <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
                <div class="border-b border-slate-100 bg-slate-50/80 px-4 py-4 sm:px-5">
                    <h2 class="font-semibold text-slate-800">Archivo (opcional)</h2>
                </div>
                <div class="px-4 py-5 sm:px-5">
                    @if($submitted)
                        <p class="text-sm text-slate-700">{{ $file_name !== '' ? $file_name : 'Sin archivo adjunto.' }}</p>
                    @else
                        <input type="file"
                            x-on:change="$wire.setFileName($event.target.files.length ? $event.target.files[0].name : '')"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-indigo-700">
                        @if($file_name !== '')
                        <p class="mt-2 text-xs text-slate-500">Seleccionado: {{ $file_name }}</p>
                        @endif
                    @endif
                </div>
            </section>
            @endif
        </div>

        @unless($submitted)
        <div class="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end sm:gap-3">
            <a href="{{ route('admin.presentation.academic.classroom.activities.index', [$subject['id'], $activity_type]) }}" wire:navigate class="w-full rounded-xl bg-slate-100 px-4 py-2.5 text-center text-sm font-medium text-slate-600 transition hover:bg-slate-200 sm:w-auto">Cancelar</a>
            <button type="submit" wire:loading.attr="disabled" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700 disabled:opacity-60 sm:w-auto">
                @if($type === 'evaluation') Enviar evaluación
                @elseif($type === 'homework') Entregar tarea
                @else Enviar ejercicio
                @endif
            </button>
        </div>
        @endunless
    </form>
</div>
