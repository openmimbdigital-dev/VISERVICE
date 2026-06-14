@extends('errors.layout')

@section('code', '419')
@section('title', 'Sesión expirada')
@section('description', 'Tu sesión ha expirado por inactividad. Por favor recarga la página o inicia sesión nuevamente.')

@section('icon')
    <svg class="h-8 w-8 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
@endsection
