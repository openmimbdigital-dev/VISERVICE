@php
    $color_map = [
        'indigo' => 'bg-indigo-500',
        'sky' => 'bg-sky-500',
        'emerald' => 'bg-emerald-500',
        'violet' => 'bg-violet-500',
        'amber' => 'bg-amber-500',
        'rose' => 'bg-rose-500',
        'teal' => 'bg-teal-500',
        'fuchsia' => 'bg-fuchsia-500',
    ];
@endphp

@if($screen === 'home')
<div class="space-y-3">
    <div class="rounded-2xl bg-indigo-600 p-4 text-white">
        <p class="text-[11px] font-medium text-indigo-100">Hola,</p>
        <p class="text-lg font-semibold">{{ $guardian['name'] }}</p>
        <p class="mt-2 text-xs text-indigo-100">Sigues a {{ $child['name'] }} · {{ $child['grade'] }}{{ $child['section'] }}</p>
    </div>

    <div class="grid grid-cols-2 gap-2">
        <button type="button" wire:click="go('classroom')" class="rounded-2xl border border-slate-200 bg-white p-3 text-left shadow-sm">
            <p class="text-sm font-semibold text-slate-900">Mi aula</p>
            <p class="mt-1 text-[11px] text-slate-500">Asignaturas y tareas</p>
        </button>
        <button type="button" wire:click="go('courses')" class="rounded-2xl border border-slate-200 bg-white p-3 text-left shadow-sm">
            <p class="text-sm font-semibold text-slate-900">Curso</p>
            <p class="mt-1 text-[11px] text-slate-500">Grupo y horario</p>
        </button>
        <button type="button" wire:click="go('enrollment')" class="rounded-2xl border border-slate-200 bg-white p-3 text-left shadow-sm">
            <p class="text-sm font-semibold text-slate-900">Inscripción</p>
            <p class="mt-1 text-[11px] text-slate-500">Documentos</p>
        </button>
        <button type="button" wire:click="go('payments')" class="rounded-2xl border border-slate-200 bg-white p-3 text-left shadow-sm">
            <p class="text-sm font-semibold text-slate-900">Pagos</p>
            <p class="mt-1 text-[11px] text-slate-500">{{ collect($invoices)->whereIn('status', ['issued', 'overdue'])->count() }} pendientes</p>
        </button>
    </div>

    @php $next_event = $events[0] ?? null; @endphp
    @if($next_event)
    <button type="button" wire:click="go('event', '{{ $next_event['id'] }}')" class="w-full rounded-2xl border border-slate-200 bg-white p-3 text-left shadow-sm">
        <p class="text-[11px] font-semibold uppercase tracking-wider text-indigo-600">Próximo evento</p>
        <p class="mt-1 text-sm font-semibold text-slate-900">{{ $next_event['title'] }}</p>
        <p class="mt-0.5 text-[11px] text-slate-500">{{ \Carbon\Carbon::parse($next_event['date'])->format('d/m') }} · {{ $next_event['start_time'] }} · {{ $next_event['location'] }}</p>
    </button>
    @endif
</div>

@elseif($screen === 'classroom')
<div class="space-y-2">
    <p class="px-1 text-xs text-slate-500">Asignaturas de {{ $child['name'] }}</p>
    @foreach($subjects as $subject)
    <button type="button" wire:click="go('subject', '{{ $subject['id'] }}')" class="flex w-full items-center gap-3 rounded-2xl border border-slate-200 bg-white p-3 text-left shadow-sm">
        <span class="h-10 w-1.5 rounded-full {{ $color_map[$subject['color'] ?? 'indigo'] ?? 'bg-indigo-500' }}"></span>
        <span class="min-w-0 flex-1">
            <span class="block text-sm font-semibold text-slate-900">{{ $subject['name'] }}</span>
            <span class="block text-[11px] text-slate-500">{{ $subject['teacher'] }} · {{ $subject['activities_count'] }} actividades</span>
        </span>
        <svg class="h-4 w-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    </button>
    @endforeach
</div>

@elseif($screen === 'subject' && $subject_record)
<div class="space-y-2">
    <div class="rounded-2xl border border-slate-200 bg-white p-3">
        <p class="text-sm font-semibold text-slate-900">{{ $subject_record['name'] }}</p>
        <p class="text-[11px] text-slate-500">{{ $subject_record['teacher'] }} · {{ $subject_record['schedule'] }}</p>
    </div>
    @forelse($subject_activities as $activity)
    <div class="rounded-2xl border border-slate-200 bg-white p-3">
        <p class="text-[11px] font-semibold text-indigo-600">{{ $activity['type_label'] }}</p>
        <p class="mt-0.5 text-sm font-medium text-slate-900">{{ $activity['title'] }}</p>
        <p class="mt-1 text-[11px] text-slate-500">Entrega {{ \Carbon\Carbon::parse($activity['due_date'])->format('d/m') }} · máx. {{ number_format((float) $activity['max_score'], 1) }}</p>
    </div>
    @empty
    <p class="py-8 text-center text-xs text-slate-500">Aún no hay actividades.</p>
    @endforelse
</div>

@elseif($screen === 'courses' || $screen === 'course')
@if($course_record)
<div class="space-y-3">
    <button type="button" @if($screen === 'courses') wire:click="go('course', '{{ $course_record['id'] }}')" @endif
        class="w-full rounded-2xl border border-slate-200 bg-white p-4 text-left shadow-sm">
        <p class="font-mono text-[11px] font-semibold text-indigo-600">{{ $course_record['code'] }}</p>
        <p class="mt-1 text-base font-semibold text-slate-900">{{ $course_record['name'] }}</p>
        <p class="mt-1 text-xs text-slate-500">{{ $course_record['grade'] }}{{ $course_record['section'] }} · {{ $course_record['shift_label'] }}</p>
    </button>
    @if($screen === 'course')
    <div class="rounded-2xl border border-slate-200 bg-white p-4">
        <dl class="space-y-3 text-sm">
            <div>
                <dt class="text-[11px] font-medium text-slate-500">Miss</dt>
                <dd class="text-slate-900">{{ $course_record['teacher'] }}</dd>
            </div>
            <div>
                <dt class="text-[11px] font-medium text-slate-500">Horario</dt>
                <dd class="text-slate-900">{{ $course_record['schedule'] }}</dd>
            </div>
            <div>
                <dt class="text-[11px] font-medium text-slate-500">Aula</dt>
                <dd class="text-slate-900">{{ $course_record['room'] }}</dd>
            </div>
            <div>
                <dt class="text-[11px] font-medium text-slate-500">Compañeros</dt>
                <dd class="text-slate-900">{{ count($course_record['students']) }} estudiantes</dd>
            </div>
        </dl>
    </div>
    @endif
</div>
@endif

@elseif($screen === 'enrollment' && $enrollment)
<div class="space-y-3">
    <div class="rounded-2xl border border-slate-200 bg-white p-4">
        <p class="text-[11px] font-semibold uppercase tracking-wider text-indigo-600">{{ $enrollment['status_label'] }}</p>
        <p class="mt-1 text-sm font-semibold text-slate-900">{{ $enrollment['child_name'] }}</p>
        <p class="text-xs text-slate-500">{{ $enrollment['grade'] }} · {{ $enrollment['shift_label'] }}</p>
        <p class="mt-2 text-xs text-slate-500">Acudiente {{ $enrollment['parent_name'] }}</p>
    </div>
    <div class="rounded-2xl border border-slate-200 bg-white p-3">
        <p class="mb-2 text-xs font-semibold text-slate-800">Documentos</p>
        <ul class="space-y-2">
            @foreach($enrollment['documents'] as $document)
            <li class="flex items-center justify-between gap-2 text-xs">
                <span class="text-slate-700">{{ $document['label'] }}</span>
                @if($document['uploaded'])
                <span class="rounded-full bg-emerald-50 px-2 py-0.5 font-medium text-emerald-700">Cargado</span>
                @else
                <span class="rounded-full bg-amber-50 px-2 py-0.5 font-medium text-amber-700">Pendiente</span>
                @endif
            </li>
            @endforeach
        </ul>
    </div>
</div>

@elseif($screen === 'payments')
<div class="space-y-2">
    @forelse($invoices as $invoice)
    <button type="button"
        @if(in_array($invoice['status'], ['issued', 'overdue'], true)) wire:click="go('pay', '{{ $invoice['id'] }}')" @endif
        class="w-full rounded-2xl border border-slate-200 bg-white p-3 text-left shadow-sm {{ in_array($invoice['status'], ['issued', 'overdue'], true) ? '' : 'opacity-80' }}">
        <div class="flex items-start justify-between gap-2">
            <div>
                <p class="text-sm font-semibold text-slate-900">{{ $invoice['concept'] }}</p>
                <p class="text-[11px] text-slate-500">{{ $invoice['number'] }} · vence {{ \Carbon\Carbon::parse($invoice['due_date'])->format('d/m') }}</p>
            </div>
            <span @class([
                'shrink-0 rounded-full px-2 py-0.5 text-[10px] font-semibold',
                'bg-emerald-50 text-emerald-700' => $invoice['status'] === 'paid',
                'bg-rose-50 text-rose-700' => $invoice['status'] === 'overdue',
                'bg-amber-50 text-amber-700' => $invoice['status'] === 'issued',
                'bg-slate-100 text-slate-600' => ! in_array($invoice['status'], ['paid', 'overdue', 'issued'], true),
            ])>{{ $invoice['status_label'] }}</span>
        </div>
        <p class="mt-2 text-sm font-semibold tabular-nums text-slate-900">{{ col_money($invoice['amount']) }}</p>
        @if(in_array($invoice['status'], ['issued', 'overdue'], true))
        <p class="mt-1 text-[11px] font-semibold text-indigo-600">Pagar ahora</p>
        @endif
    </button>
    @empty
    <p class="py-8 text-center text-xs text-slate-500">No hay facturas.</p>
    @endforelse
</div>

@elseif($screen === 'pay' && $invoice_record)
<div class="space-y-3">
    <div class="rounded-2xl border border-slate-200 bg-white p-4">
        <p class="text-sm font-semibold text-slate-900">{{ $invoice_record['concept'] }}</p>
        <p class="text-[11px] text-slate-500">{{ $invoice_record['number'] }}</p>
        <p class="mt-3 text-2xl font-semibold tabular-nums text-slate-900">{{ col_money($invoice_record['amount']) }}</p>
    </div>
    <div class="rounded-2xl border border-slate-200 bg-white p-4">
        <label class="mb-1.5 block text-[11px] font-medium text-slate-700">Medio de pago</label>
        <select wire:model="pay_method" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm">
            <option value="Transferencia">Transferencia</option>
            <option value="Tarjeta">Tarjeta</option>
            <option value="PSE">PSE</option>
        </select>
        <button type="button" wire:click="confirmPay" class="mt-4 w-full rounded-xl bg-indigo-600 py-2.5 text-sm font-semibold text-white">Confirmar pago</button>
    </div>
</div>

@elseif($screen === 'events')
<div class="space-y-2">
    @foreach($events as $event)
    <button type="button" wire:click="go('event', '{{ $event['id'] }}')" class="w-full rounded-2xl border border-slate-200 bg-white p-3 text-left shadow-sm">
        <p class="text-sm font-semibold text-slate-900">{{ $event['title'] }}</p>
        <p class="mt-1 text-[11px] text-slate-500">{{ \Carbon\Carbon::parse($event['date'])->format('d/m/Y') }} · {{ $event['start_time'] }} – {{ $event['end_time'] }}</p>
        <p class="text-[11px] text-slate-500">{{ $event['location'] }}</p>
    </button>
    @endforeach
</div>

@elseif($screen === 'event' && $event_record)
<div class="rounded-2xl border border-slate-200 bg-white p-4">
    <p class="text-base font-semibold text-slate-900">{{ $event_record['title'] }}</p>
                <p class="mt-2 text-xs text-slate-500">{{ \Carbon\Carbon::parse($event_record['date'])->format('d/m/Y') }}</p>
    <p class="text-xs text-slate-500">{{ $event_record['start_time'] }} – {{ $event_record['end_time'] }} · {{ $event_record['location'] }}</p>
    <p class="mt-4 text-sm leading-relaxed text-slate-700">{{ $event_record['description'] }}</p>
</div>
@endif
