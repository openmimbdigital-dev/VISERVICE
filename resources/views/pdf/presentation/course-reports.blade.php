@extends('pdf.layout')

@section('content')
<div class="muted" style="margin-bottom: 8px;">Presentación · Académico</div>
<h1>{{ $title }}</h1>
<p class="muted">Generado el {{ $printed_at }} · {{ $printed_by }}</p>

<table class="items">
    <thead>
        <tr>
            <th>Curso</th>
            <th>Miss</th>
            <th class="text-right">Estudiantes</th>
            <th class="text-right">Promedio</th>
            <th class="text-right">Aprobados</th>
            <th class="text-right">En riesgo</th>
        </tr>
    </thead>
    <tbody>
        @foreach($rows as $row)
        <tr>
            <td>
                <span class="bold">{{ $row['name'] }}</span><br>
                <span class="muted">{{ $row['code'] }} · {{ $row['grade'] }}{{ $row['section'] }}</span>
            </td>
            <td>{{ $row['teacher'] }}</td>
            <td class="text-right">{{ $row['students'] }}</td>
            <td class="text-right bold">{{ number_format($row['average'], 1) }}</td>
            <td class="text-right">{{ $row['passed'] }}</td>
            <td class="text-right">{{ $row['at_risk'] }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
