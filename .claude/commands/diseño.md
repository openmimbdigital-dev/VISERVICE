# Sistema de diseño VISERVICE

Eres un asistente experto en el proyecto VISERVICE. Cuando el usuario invoque este comando debes tener presentes TODAS las convenciones de diseño y arquitectura descritas aquí. Nunca las ignores ni las cambies sin que el usuario lo pida explícitamente.

---

## Stack

| Capa | Tecnología |
|------|-----------|
| Backend | Laravel 12 |
| Frontend reactivo | Livewire 3 |
| CSS | Tailwind CSS v4 (`@tailwindcss/vite`) |
| JS interactivo | Alpine.js 3 |
| Tablas de datos | `arm092/livewire-datatables ^2.3` |
| Permisos | Spatie Laravel Permission v6 |
| Alertas | `jantinnerezo/livewire-alert` + SweetAlert2 |
| Acciones | `lorisleiva/laravel-actions` v2 |
| Build | Vite 7 |

---

## Paleta de colores (definida en `resources/css/app.css` con `@theme`)

- **Primary** → `#1C22AA` (azul índigo oscuro) — botones principales, acentos
- **Slate-900** → `#121317` — fondo sidebar
- **Slate-100** → fondo general de la app (`bg-slate-100`)
- **Indigo-400/500/600** → íconos del sidebar, acentos en headings
- **Emerald-400/600** → sección Taller, estados activos/positivos
- **Amber-400/600** → sección Catálogo
- **Violet-600** → sección Suscripciones
- **Red-600** → acciones destructivas
- **White** → fondos de tarjetas, topbar

---

## Layout general (`resources/views/layouts/app.blade.php`)

```
┌─────────────────────────────────────────────┐
│  <aside> sidebar oscuro (slate-900)          │
│  w-60 expandido / w-[4.25rem] colapsado      │
│  ┌─ Logo VISERVICE (logo-initial.png)        │
│  ├─ nav: Inicio                              │
│  ├─ nav: Usuarios (acordeón)                 │
│  ├─ nav: Suscripciones (solo superAdmin)     │
│  ├─ nav: Taller (acordeón, íconos emerald)   │
│  └─ nav: Catálogo (acordeón, íconos amber)   │
├─────────────────────────────────────────────┤
│  <header> topbar h-14 bg-white/95            │
│  ├─ Izquierda: ícono cuadrado + título       │
│  ├─ Centro: [vacío]                          │
│  └─ Derecha: badge "En línea" + menú usuario │
├─────────────────────────────────────────────┤
│  <main> flex-1 p-4 lg:p-6 overflow-auto      │
│  {{ $slot }}                                 │
└─────────────────────────────────────────────┘
```

### Clases clave del sidebar
- Fondo: `bg-slate-900 text-slate-100`
- Íconos activos: `bg-slate-800 text-white`
- Íconos hover: `hover:bg-slate-800/80 hover:text-white`
- Submenú: `border-l border-slate-700/80 ml-4 pl-3`
- El sidebar usa Alpine.js para colapsar/expandir, estado en `localStorage`

### Clases clave del topbar
- `relative z-20` — imprescindible para que el dropdown quede por encima del contenido
- Avatar del usuario: gradiente `from-indigo-500 to-indigo-700` con iniciales en blanco
- Dropdown: header con gradiente indigo + badge de rol + botón cerrar sesión

---

## Login (`resources/views/auth/login.blade.php`)

Diseño **split-screen**:
- **Panel izquierdo** (solo `lg:`) — `bg-gradient-to-br from-slate-900 via-indigo-950 to-slate-900`, fondo de puntos CSS (`radial-gradient`), logo con animación `float`, orbes de color con `blur-3xl`, 3 tarjetas de módulos con `bg-white/5 backdrop-blur-sm`
- **Panel derecho** — `bg-white`, formulario centrado con `max-w-sm`
  - Inputs: `rounded-xl border border-slate-200 bg-slate-50 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20`
  - Botón submit: `bg-indigo-600 hover:bg-indigo-700 rounded-xl`
  - Toggle mostrar/ocultar contraseña con Alpine (`x-data="{ show: false }"`)

---

## Dashboard (`resources/views/livewire/dashboard.blade.php`)

1. **Hero** — `rounded-2xl bg-gradient-to-br from-slate-900 via-indigo-950 to-slate-900` con logo, saludo y fecha
2. **Grid de módulos** — `grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4`
   - Cada módulo: tarjeta con borde de color (`bg-emerald-50 border-emerald-100`, etc.), ícono en cuadrado coloreado, links internos con chevron
3. **Barra de estado** — `border border-slate-200 bg-white` con punto verde pulsante

---

## Páginas de índice (patrón estándar)

Cada módulo tiene esta estructura en su blade:

```blade
<div class="relative mx-auto w-full max-w-[90rem]">
    {{-- Breadcrumb --}}
    <nav class="mb-6 flex items-center gap-x-2 text-xs ...">...</nav>

    {{-- Header con KPIs --}}
    <header class="mb-8">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-stretch lg:justify-between">
            <div class="min-w-0 flex-1 border-l-4 border-indigo-600 pl-5">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-indigo-600/90">Módulo</p>
                <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900">Título</h1>
                <p class="mt-2 max-w-xl text-sm text-slate-600">Descripción.</p>
            </div>
            <div class="grid shrink-0 grid-cols-2 gap-3 sm:max-w-xs">
                {{-- KPI cards --}}
            </div>
        </div>
    </header>

    {{-- Sección datatable --}}
    <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
        <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50/80 px-5 py-4">
            <h2 class="font-semibold text-slate-800">Listado</h2>
            <button wire:click="openCreate" class="btn btn-primary btn-sm">Nuevo</button>
        </div>
        <div class="p-4">
            <livewire:ruta.al.datatable />
        </div>
    </section>

    {{-- Modal --}}
    <div x-show="$wire.showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" wire:click="closeModal"></div>
        <div class="relative w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl" @click.stop>
            ...
        </div>
    </div>
</div>
```

---

## Datatables (`arm092/livewire-datatables`)

### Requisito crítico — publicar vistas y config
```bash
php artisan vendor:publish --provider="Arm092\LivewireDatatables\LivewireDatatablesServiceProvider" --tag=views
php artisan vendor:publish --provider="Arm092\LivewireDatatables\LivewireDatatablesServiceProvider" --tag=config
```
Las vistas se publican en `resources/views/livewire/datatables/` para que Tailwind v4 las escanee.
Sin esto las clases `table`, `table-row`, `table-cell`, `divide-x` etc. no se generan (Tailwind v4 respeta `.gitignore` que excluye `vendor/`).

### Estructura de una clase Datatable
```php
class DatatableXxx extends LivewireDatatable
{
    public bool $exportable = true;
    public ?int $perPage = 25;

    public function builder(): Builder
    {
        return Xxx::where('business_id', auth()->user()->business_id)
            ->orderBy('name');
    }

    public function getColumns(): array
    {
        return [
            Column::name('name')->label('Nombre')->searchable()->sortable(),
            Column::callback(['status'], fn($s) => '<span class="...">...</span>')->label('Estado')->filterable([1=>'Activo',0=>'Inactivo']),
            DateColumn::name('created_at')->label('Fecha')->sortable(),
            Column::callback(['id'], fn($id) => view('livewire.ruta.actions', ['id'=>$id]))->label('Acciones')->unsortable(),
        ];
    }

    public function openEditEvent(int $id): void
    {
        $this->dispatch('open-xxx-edit', id: $id);
    }

    public function render()
    {
        $this->dispatch('refreshDynamic');
        if ($this->persistPerPage) session()->put([$this->sessionStorageKey().'_perpage' => $this->perPage]);
        return view('datatables::datatable');
    }
}
```

### Clase Index correspondiente
```php
class Index extends Component
{
    public bool $showModal = false;
    // propiedades del formulario...

    #[On('open-xxx-edit')]
    public function openEdit(int $id): void { /* carga datos */ $this->showModal = true; }

    #[On('xxx-deleted')]
    public function onRecordDeleted(): void {} // vacío, solo fuerza re-render

    public function render(): View
    {
        return view('livewire.admin.modulo.xxx.index', [
            'stats' => [...],
        ]);
    }
}
```

### Vista de acciones (actions.blade.php)
```blade
<div class="flex items-center gap-1">
    <button wire:click="openEditEvent({{ $id }})" class="rounded-lg p-1.5 text-slate-400 hover:bg-indigo-50 hover:text-indigo-600 transition">
        {{-- ícono lápiz --}}
    </button>
    <button wire:click="deleteRecord({{ $id }})" wire:confirm="¿Eliminar este registro?" class="rounded-lg p-1.5 text-slate-400 hover:bg-red-50 hover:text-red-600 transition">
        {{-- ícono basura --}}
    </button>
</div>
```

---

## Clases de utilidad definidas en `app.css`

```
.btn                  — base de botón
.btn-primary          — azul (bg-blue-600)
.btn-secondary        — gris
.btn-success          — verde
.btn-danger           — rojo
.btn-warning          — amarillo
.btn-info             — cyan
.btn-outline-primary  — outline azul
.btn-sm / .btn-lg / .btn-xl
```

En los formularios usar:
- `class="form-input"` — inputs de texto
- `class="form-select"` — selects
- `class="label-up"` — labels
- `class="custom-checkbox"` — checkboxes

---

## Convenciones generales

- Todo el texto de la UI en **español**
- Variables en **snake_case**
- Eventos Livewire: `$this->dispatch('swal', ['title'=>'...','icon'=>'success'])`
- Confirmaciones: `wire:confirm="¿Eliminar?"` (nativo Livewire 3, no modal)
- Multi-tenant: **siempre** filtrar por `auth()->user()->business_id` en los queries
- Roles: `superAdmin`, `Administrador`, `Supervisor`, `Operador`
- Permisos: definidos en `config/permissions.php`
- Logo en `public/images/logo-initial.png`

---

## Comandos útiles al clonar/instalar el proyecto

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan vendor:publish --provider="Arm092\LivewireDatatables\LivewireDatatablesServiceProvider" --tag=views
php artisan vendor:publish --provider="Arm092\LivewireDatatables\LivewireDatatablesServiceProvider" --tag=config
npm run dev
php artisan serve
```
