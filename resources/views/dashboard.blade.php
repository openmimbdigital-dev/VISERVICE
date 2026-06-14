<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - VISERVICE</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 min-h-screen">
    <main class="max-w-4xl mx-auto py-10 px-4">
        <div class="bg-white rounded-xl shadow p-6">
            <h1 class="text-2xl font-semibold text-gray-900">Bienvenido a VISERVICE</h1>
            <p class="mt-2 text-gray-600">
                Sesion iniciada como: <strong>{{ $user?->name ?? $user?->email }}</strong>
            </p>

            <form class="mt-6" method="POST" action="{{ route('logout') }}">
                @csrf
                <button
                    type="submit"
                    class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition"
                >
                    Cerrar sesion
                </button>
            </form>
        </div>
    </main>
</body>
</html>
