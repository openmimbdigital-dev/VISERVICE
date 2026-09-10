@extends('pdf.layout')

@section('content')
<div class="muted" style="margin-bottom: 8px;">Presentación · Académico</div>
<h1>{{ $title }}</h1>
<p class="muted">Generado el {{ $printed_at }} · {{ $printed_by }}@if($course_name) · {{ $course_name }}@endif</p>

<table class="items">
    <thead>
        <tr>
            <th>Estudiante</th>
            <th>Curso</th>
            <th class="text-right">Evaluaciones</th>
            <th class="text-right">Tareas</th>
            <th class="text-right">Ejercicios</th>
            <th class="text-right">Promedio</th>
            <th>Estado</th>
        </tr>
    </thead>
    <tbody>
        @foreach($rows as $row)
        <tr>
            <td>
                <span class="bold">{{ $row['name'] }}</span><br>
                <span class="muted">{{ $row['document'] }}</span>
            </td>
            <td>{{ $row['course_name'] }}</td>
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
