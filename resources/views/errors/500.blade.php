@extends('errors.layout')

@section('code', '500')
@section('title', 'Error interno del servidor')
@section('description', 'Algo salió mal en el servidor. Nuestro equipo ya fue notificado. Intenta de nuevo en unos minutos.')

@section('icon')
    <svg class="h-8 w-8 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
    </svg>
@endsection
