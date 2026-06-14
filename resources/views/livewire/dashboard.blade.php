<div class="max-w-4xl">
    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-xl font-semibold text-slate-900">Bienvenido a VISERVICE</h2>
        <p class="mt-2 text-slate-600">
            Sesión iniciada como
            <strong class="text-slate-800">{{ auth()->user()?->name ?? auth()->user()?->username ?? auth()->user()?->email }}</strong>.
        </p>
        <p class="mt-4 text-sm text-slate-500">
            Usa el menú lateral para navegar. Puedes encoger la barra con el botón inferior del panel.
        </p>
    </div>
</div>
