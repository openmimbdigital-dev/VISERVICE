@extends('pdf.layout')

@section('content')
<style>
    .report-line {
        height: 4px;
        background: #4f46e5;
        margin: 0 0 16px;
        border: 0;
    }
    .report-badge {
        display: inline-block;
        background: #eef2ff;
        color: #4338ca;
        font-size: 9px;
        font-weight: bold;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        padding: 4px 8px;
        margin-bottom: 8px;
    }
    .meta-table td { padding: 3px 0; vertical-align: top; }
    .meta-table .label { width: 110px; color: #64748b; font-size: 10px; }
    .chart-wrap { margin-top: 10px; }
    .chart-row { margin-bottom: 8px; }
    .chart-label {
        font-size: 10px;
        font-weight: bold;
        margin-bottom: 2px;
    }
    .chart-track {
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
        height: 16px;
        position: relative;
    }
    .chart-bar {
        background: #4f46e5;
        height: 16px;
    }
    .chart-value {
        font-size: 10px;
        color: #4338ca;
        font-weight: bold;
        margin-top: 2px;
    }
    .footer-meta {
        margin-top: 24px;
        border-top: 1px solid #cbd5e1;
        padding-top: 10px;
        font-size: 10px;
        color: #64748b;
    }
    .logo-cell { width: 90px; vertical-align: top; padding-right: 12px; }
    .logo-img { width: 72px; height: 72px; object-fit: contain; border: 1px solid #e2e8f0; padding: 4px; }
    .logo-fallback {
        width: 72px;
        height: 72px;
        background: #eef2ff;
        color: #4338ca;
        font-size: 20px;
        font-weight: bold;
        text-align: center;
        line-height: 72px;
        border: 1px solid #c7d2fe;
    }
</style>

<div class="report-badge">Informe de asistencia</div>
<div class="report-line"></div>

<table class="header">
    <tr>
        <td style="width: 62%;">
            <table style="width:100%; border-collapse: collapse;">
                <tr>
                    <td class="logo-cell">
                        @if($logo_path)
                            <img src="{{ $logo_path }}" alt="Logo" class="logo-img">
                        @else
                            <div class="logo-fallback">{{ strtoupper(mb_substr($business?->name ?? 'VS', 0, 2)) }}</div>
                        @endif
                    </td>
                    <td style="vertical-align: top;">
                        <h1>{{ $business?->name ?? '—' }}</h1>
                        <p class="muted">{{ $entity_label }}</p>
                        @if($business?->nit)<p class="muted">NIT: {{ $business->nit }}</p>@endif
                        @if($business?->address)<p class="muted">{{ $business->address }}</p>@endif
                        @if($business?->phone_number)<p class="muted">Tel: {{ $business->phone_number }}</p>@endif
                        @if($business?->email)<p class="muted">{{ $business->email }}</p>@endif
                    </td>
                </tr>
            </table>
        </td>
        <td class="header-right">
            <h2>ASISTENCIA DE EVENTO</h2>
            <p class="ref">#{{ $event->id }}</p>
            <p class="muted">Generado: {{ $printed_at }}</p>
        </td>
    </tr>
</table>

<div class="section">
    <div class="section-title">Datos del evento</div>
    <table class="meta-table" style="width:100%;">
        <tr>
            <td class="label">Nombre</td>
            <td class="bold">{{ $event->name }}</td>
        </tr>
        @if($event->isMultiDayChild())
            <tr>
                <td class="label">Tipo</td>
                <td>Día de evento multi-día</td>
            </tr>
            @if($event->parent)
                <tr>
                    <td class="label">Evento padre</td>
                    <td>{{ $event->parent->name }} ({{ $event->parent->dateRangeLabel() }})</td>
                </tr>
            @endif
        @endif
        <tr>
            <td class="label">Categoría</td>
            <td>{{ $event->category?->name ?? '—' }}</td>
        </tr>
        <tr>
            <td class="label">Fecha</td>
            <td>{{ $event->dateRangeLabel() }} · {{ $event->day ?: '—' }}</td>
        </tr>
        <tr>
            <td class="label">Horario</td>
            <td>{{ $event->scheduleRangeLabel() }}</td>
        </tr>
        <tr>
            <td class="label">Equipos</td>
            <td>
                @if($event->teams->isEmpty())
                    Sin equipos asignados
                @else
                    {{ $event->teams->pluck('name')->join(', ') }}
                @endif
            </td>
        </tr>
        @if($multi_day_context)
            <tr>
                <td class="label">Aclaración</td>
                <td>{{ $multi_day_context }} La asistencia de este informe corresponde únicamente a este día.</td>
            </tr>
        @endif
        @if($event->description)
            <tr>
                <td class="label">Descripción</td>
                <td>{{ $event->description }}</td>
            </tr>
        @endif
    </table>
</div>

<div class="section">
    <div class="section-title">Resumen</div>
    <p><span class="bold">Asistencia total:</span> {{ number_format($attendance_total, 0, ',', '.') }}</p>
    <p><span class="bold">Tipos registrados:</span> {{ $attendance_rows->count() }}</p>
    @if($multi_day_context)
        <p class="muted">Gráfico y totales limitados al día reportado del evento multi-día.</p>
    @endif
</div>

<div class="section">
    <div class="section-title">Gráfico de asistencia</div>
    @if($attendance_rows->isEmpty())
        <p class="muted">Sin datos de asistencia para graficar.</p>
    @else
        <div class="chart-wrap">
            @foreach($attendance_rows as $index => $attendee_type)
                @php
                    $value = (int) $attendee_type->pivot->attendance;
                    $width = (int) round(($value / $max_attendance) * 100);
                @endphp
                <div class="chart-row">
                    <div class="chart-label">{{ $attendee_type->name }}</div>
                    <div class="chart-track">
                        <div class="chart-bar" style="width: {{ max($width, $value > 0 ? 4 : 0) }}%;"></div>
                    </div>
                    <div class="chart-value">{{ number_format($value, 0, ',', '.') }}</div>
                </div>
            @endforeach
        </div>
    @endif
</div>

<div class="section">
    <div class="section-title">Detalle de asistencia</div>
    <table class="items">
        <thead>
            <tr>
                <th>Tipo de asistencia</th>
                <th>Rango de edad</th>
                <th class="text-right">Asistencia</th>
            </tr>
        </thead>
        <tbody>
            @forelse($attendance_rows as $attendee_type)
                <tr>
                    <td>{{ $attendee_type->name }}</td>
                    <td>{{ $attendee_type->ageRangeLabel() }}</td>
                    <td class="text-right bold">{{ number_format((int) $attendee_type->pivot->attendance, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="muted">Este evento aún no tiene toma de asistencia registrada.</td>
                </tr>
            @endforelse
            @if($attendance_rows->isNotEmpty())
                <tr>
                    <td colspan="2" class="bold">Total</td>
                    <td class="text-right bold">{{ number_format($attendance_total, 0, ',', '.') }}</td>
                </tr>
            @endif
        </tbody>
    </table>
</div>

<div class="footer-meta">
    <p><span class="bold">Impreso por:</span> {{ $printed_by }}</p>
    <p><span class="bold">Rol:</span> {{ $printed_by_roles }}</p>
    <p><span class="bold">{{ $entity_label }}:</span> {{ $business?->name ?? '—' }}</p>
</div>
@endsection
