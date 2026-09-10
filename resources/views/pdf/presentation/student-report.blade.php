@extends('pdf.layout')

@section('content')
<div class="muted" style="margin-bottom: 8px;">Presentación · Académico</div>
<h1>{{ $title }}</h1>
<p class="muted">{{ $report['period'] }} · Generado el {{ $printed_at }} · {{ $printed_by }}</p>

<table class="grid">
    <tr>
        <td>
            <div class="section-title">Estudiante</div>
            <p class="bold" style="margin:0">{{ $report['name'] }}</p>
            <p class="muted" style="margin:4px 0 0">Documento {{ $report['document'] }}</p>
            <p class="muted" style="margin:4px 0 0">{{ $report['email'] }}</p>
        </td>
        <td>
            <div class="section-title">Curso</div>
            <p class="bold" style="margin:0">{{ $report['course_name'] }}</p>
            <p class="muted" style="margin:4px 0 0">{{ $report['grade'] }}{{ $report['section'] }} · {{ $report['shift_label'] }}</p>
            <p class="muted" style="margin:4px 0 0">Miss {{ $report['teacher'] }}</p>
        </td>
    </tr>
</table>

<table class="items">
    <thead>
        <tr>
            <th>Actividad</th>
            <th>Tipo</th>
            <th class="text-right">Nota</th>
            <th class="text-right">Máximo</th>
        </tr>
    </thead>
    <tbody>
        @foreach($report['items'] as $item)
        <tr>
            <td>{{ $item['name'] }}</td>
            <td>{{ $item['type_label'] }}</td>
            <td class="text-right bold">{{ number_format($item['score'], 1) }}</td>
            <td class="text-right">{{ number_format($item['max_score'], 1) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<table class="totals">
    <tr>
        <td>Evaluaciones</td>
        <td class="text-right">{{ number_format($report['evaluations'], 1) }}</td>
    </tr>
    <tr>
        <td>Tareas</td>
        <td class="text-right">{{ number_format($report['homework'], 1) }}</td>
    </tr>
    <tr>
        <td>Ejercicios en clase</td>
        <td class="text-right">{{ number_format($report['class_exercises'], 1) }}</td>
    </tr>
    <tr class="total">
        <td>Promedio</td>
        <td class="text-right">{{ number_format($report['average'], 1) }} · {{ $report['status'] }}</td>
    </tr>
</table>
@endsection
