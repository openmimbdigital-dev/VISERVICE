@extends('errors.layout')

@section('code', '404')
@section('title', 'Página no encontrada')
@section('description', 'La página que buscas no existe o fue movida. Verifica la URL o regresa al inicio.')

@section('icon')
    <svg class="h-8 w-8 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
@endsection
