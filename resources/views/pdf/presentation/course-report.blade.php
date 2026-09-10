@extends('pdf.layout')

@section('content')
<div class="muted" style="margin-bottom: 8px;">Presentación · Académico</div>
<h1>{{ $title }}</h1>
<p class="muted">Generado el {{ $printed_at }} · {{ $printed_by }}</p>

<table class="grid">
    <tr>
        <td>
            <div class="section-title">Curso</div>
            <p class="bold" style="margin:0">{{ $report['name'] }} ({{ $report['code'] }})</p>
            <p class="muted" style="margin:4px 0 0">{{ $report['grade'] }}{{ $report['section'] }} · {{ $report['shift_label'] }}</p>
        </td>
        <td>
            <div class="section-title">Resumen</div>
            <p style="margin:0">Miss: {{ $report['teacher'] }}</p>
            <p style="margin:4px 0 0">Promedio {{ number_format($report['average'], 1) }} · {{ $report['passed'] }} aprobados · {{ $report['at_risk'] }} en riesgo</p>
        </td>
    </tr>
</table>

<table class="items">
    <thead>
        <tr>
            <th>Estudiante</th>
            <th>Documento</th>
            <th class="text-right">Evaluaciones</th>
            <th class="text-right">Tareas</th>
            <th class="text-right">Ejercicios</th>
            <th class="text-right">Promedio</th>
            <th>Estado</th>
        </tr>
    </thead>
    <tbody>
        @foreach($report['student_rows'] as $row)
        <tr>
            <td class="bold">{{ $row['name'] }}</td>
            <td>{{ $row['document'] }}</td>
            <td class="text-right">{{ number_format($row['evaluations'], 1) }}</td>
            <td class="text-right">{{ number_format($row['homework'], 1) }}</td>
            <td class="text-right">{{ number_format($row['class_exercises'], 1) }}</td>
            <td class="text-right bold">{{ number_format($row['average'], 1) }}</td>
            <td>{{ $row['status'] }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
