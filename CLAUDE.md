# VISERVICE — Guía de arquitectura para Claude

## Stack
- **Laravel 12** + **Livewire 3** + **Tailwind CSS v4** + **Alpine.js 3**
- **Spatie Laravel Permission v6** — roles y permisos
- **Vite 7** — build tool
- **SweetAlert2** — alertas (expuesto como `window.Swal`)
- **ApexCharts** — gráficas (expuesto como `window.ApexCharts`)

## Convenciones
- Variables en **snake_case** (ver README)
- **Código en inglés** (permisos, rutas, clases, columnas); **UI en español** (Blade, títulos, labels de permisos)
- Ejemplo permiso: `'events.schedule.view' => 'Ver agenda de eventos'` — clave EN (agenda→schedule), etiqueta ES
- Clases CSS: usar clases `.btn`, `.btn-primary`, `.btn-danger`, etc. (definidas en `resources/css/app.css`)
- Eventos SweetAlert desde Livewire: `$this->dispatch('swal', ['title' => '...', 'icon' => 'success'])`

## Estructura de carpetas
```
app/
  Http/
    Controllers/    AuthController
    Middleware/     CheckPermission, CheckRole, CheckActiveSubscription
  Livewire/
    Admin/
      Subscriptions/
        Index.php          — Gestión de suscripciones por comercio
        Plans/Index.php    — CRUD de planes
      Roles/Index.php
      User/Index.php
  Models/
    Business.php           — Comercio (tiene suscripciones)
    Subscription.php       — Suscripción activa de un Business
    SubscriptionPlan.php   — Paquete/plan disponible
    SubscriptionInvoice.php — Facturas/pagos por período
    User.php
config/
  permissions.php    — Módulos y permisos del sistema
routes/
  web.php
```

## Modelo de suscripciones

### Flujo principal
1. SuperAdmin crea **planes** (`subscription_plans`) con precio mensual, descuentos por ciclo y features
2. SuperAdmin asigna una **suscripción** a un Business: elige plan + ciclo de facturación
3. El sistema calcula fechas y precio total con descuento aplicado
4. Se genera una **factura** automáticamente (estado: `pending`)
5. SuperAdmin registra el pago → factura pasa a `paid`

### Ciclos de facturación
| Ciclo | Meses | Descuento default |
|---|---|---|
| `monthly` | 1 | 0% |
| `quarterly` | 3 | 5% |
| `semiannual` | 6 | 10% |
| `annual` | 12 | 20% |

Los descuentos son configurables por plan en `billing_cycles` (JSON).

### Estados de suscripción
| Estado | Descripción |
|---|---|
| `trial` | Período de prueba (sin cobro) |
| `active` | Suscripción activa y paga |
| `past_due` | Vencida en pago, sigue activa temporalmente |
| `cancelled` | Cancelada manualmente |
| `expired` | Fecha de fin superada sin renovar |

### Estados de factura
`pending` → `paid` / `failed` / `refunded`

### Middleware
- `active.subscription` → verifica que el Business tenga suscripción activa (superAdmin siempre pasa)
- Aplicar a rutas de módulos funcionales del negocio

## Roles disponibles
- `superAdmin` — acceso total, gestiona suscripciones
- `Administrador` — admin del negocio
- `Supervisor`
- `Operador`

## Permisos relevantes (config/permissions.php)
- `subscriptions.view/create/edit/cancel`
- `subscriptions.plans.view/manage`
- `subscriptions.invoices.view/manage`
- `users.view/create/edit/delete`
- `roles.view/create/edit/delete`

## Rutas de suscripciones
```
GET /admin/subscriptions         → admin.subscriptions.index   (role:superAdmin)
GET /admin/subscriptions/plans   → admin.subscriptions.plans.index (role:superAdmin)
```

## Notas importantes
- El `Business` tiene `hasActiveSubscription()` para verificar acceso
- `SubscriptionPlan::getPriceForCycle($cycle)` devuelve `{months, discount, base, total}`
- `SubscriptionInvoice::generateInvoiceNumber()` genera número secuencial `INV-YYYYMM-XXXX`
- Al crear suscripción no-trial, se genera factura `pending` automáticamente
- Al registrar pago, si el estado era `trial` o `past_due`, pasa a `active`
