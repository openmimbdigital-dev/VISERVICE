<div class="space-y-6">

        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Planes de Suscripción</h2>
                <p class="text-sm text-gray-500 mt-1">Define los paquetes disponibles para los comercios</p>
            </div>
            <button wire:click="openCreate" class="btn btn-primary">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Nuevo Plan
            </button>
        </div>

        {{-- Cards de planes --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($plans as $plan)
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden {{ !$plan->is_active ? 'opacity-60' : '' }}">
                    <div class="p-5 border-b border-gray-100 flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">{{ $plan->name }}</h3>
                            <p class="text-2xl font-bold text-blue-600 mt-1">
                                ${{ number_format($plan->monthly_price, 0, ',', '.') }}
                                <span class="text-sm font-normal text-gray-500">/mes</span>
                            </p>
                        </div>
                        <div class="flex flex-col items-end gap-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $plan->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                {{ $plan->is_active ? 'Activo' : 'Inactivo' }}
                            </span>
                            <span class="text-xs text-gray-400">{{ $plan->active_subscriptions_count }} activas</span>
                        </div>
                    </div>

                    <div class="p-5 space-y-3">
                        @if($plan->description)
                            <p class="text-sm text-gray-600">{{ $plan->description }}</p>
                        @endif

                        @if($plan->max_users)
                            <p class="text-sm text-gray-500">
                                <svg class="w-4 h-4 inline mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197"/>
                                </svg>
                                Hasta {{ $plan->max_users }} usuarios
                            </p>
                        @endif

                        {{-- Ciclos y precios --}}
                        <div class="space-y-1">
                            @foreach($plan->getCycles() as $key => $cycle)
                                @php $priceData = $plan->getPriceForCycle($key); @endphp
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-500">{{ $cycle['label'] }}</span>
                                    <span class="font-medium text-gray-800">
                                        ${{ number_format($priceData['total'], 0, ',', '.') }}
                                        @if($cycle['discount'] > 0)
                                            <span class="text-green-600 text-xs">({{ $cycle['discount'] }}% dto)</span>
                                        @endif
                                    </span>
                                </div>
                            @endforeach
                        </div>

                        @if($plan->features && count($plan->features))
                            <ul class="space-y-1 pt-2 border-t border-gray-100">
                                @foreach($plan->features as $feature)
                                    <li class="flex items-center text-sm text-gray-600">
                                        <svg class="w-4 h-4 text-green-500 mr-2 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        {{ $feature }}
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>

                    <div class="px-5 py-3 bg-gray-50 border-t border-gray-100 flex justify-between items-center">
                        <div class="flex gap-2">
                            <button wire:click="openEdit({{ $plan->id }})" class="btn btn-sm btn-outline-primary">Editar</button>
                            <button wire:click="toggleActive({{ $plan->id }})" class="btn btn-sm {{ $plan->is_active ? 'btn-warning' : 'btn-success' }}">
                                {{ $plan->is_active ? 'Desactivar' : 'Activar' }}
                            </button>
                        </div>
                        @if($plan->subscriptions_count === 0)
                            <button wire:click="delete({{ $plan->id }})"
                                wire:confirm="¿Eliminar este plan?"
                                class="btn btn-sm btn-danger">
                                Eliminar
                            </button>
                        @endif
                    </div>
                </div>
            @empty
                <div class="col-span-3 text-center py-16 text-gray-400">
                    <svg class="w-12 h-12 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p class="font-medium">No hay planes creados</p>
                    <p class="text-sm mt-1">Crea el primer plan de suscripción</p>
                </div>
            @endforelse
        </div>

        {{ $plans->links() }}
    </div>

    {{-- Modal crear/editar plan --}}
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4" x-data x-init="$el.focus()">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto" @click.stop>
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">
                        {{ $editing ? 'Editar Plan' : 'Nuevo Plan' }}
                    </h3>
                    <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form wire:submit="save" class="p-6 space-y-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nombre del plan *</label>
                            <input type="text" wire:model="name" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Precio mensual (COP) *</label>
                            <input type="number" wire:model="monthly_price" step="0.01" min="0" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @error('monthly_price') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                        <textarea wire:model="description" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Máx. usuarios</label>
                            <input type="number" wire:model="max_users" min="1" placeholder="Ilimitado" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Orden</label>
                            <input type="number" wire:model="sort_order" min="0" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <div class="flex items-center pt-5">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" wire:model="is_active" class="custom-checkbox">
                                <span class="text-sm font-medium text-gray-700">Activo</span>
                            </label>
                        </div>
                    </div>

                    {{-- Descuentos por ciclo --}}
                    <div>
                        <p class="text-sm font-medium text-gray-700 mb-2">Descuentos por ciclo de facturación (%)</p>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                            <div>
                                <label class="text-xs text-gray-500">Mensual</label>
                                <input type="number" wire:model="discount_monthly" step="0.5" min="0" max="100" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                            </div>
                            <div>
                                <label class="text-xs text-gray-500">Trimestral</label>
                                <input type="number" wire:model="discount_quarterly" step="0.5" min="0" max="100" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                            </div>
                            <div>
                                <label class="text-xs text-gray-500">Semestral</label>
                                <input type="number" wire:model="discount_semiannual" step="0.5" min="0" max="100" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                            </div>
                            <div>
                                <label class="text-xs text-gray-500">Anual</label>
                                <input type="number" wire:model="discount_annual" step="0.5" min="0" max="100" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                            </div>
                        </div>
                    </div>

                    {{-- Features --}}
                    <div>
                        <p class="text-sm font-medium text-gray-700 mb-2">Características incluidas</p>
                        <div class="flex gap-2 mb-2">
                            <input type="text" wire:model="new_feature" wire:keydown.enter.prevent="addFeature"
                                placeholder="Ej: Reportes avanzados" class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <button type="button" wire:click="addFeature" class="btn btn-sm btn-secondary">Agregar</button>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            @foreach($features as $i => $feature)
                                <span class="inline-flex items-center gap-1 bg-blue-50 text-blue-700 text-sm px-3 py-1 rounded-full">
                                    {{ $feature }}
                                    <button type="button" wire:click="removeFeature({{ $i }})" class="text-blue-400 hover:text-blue-700 ml-1">×</button>
                                </span>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-2 border-t border-gray-100">
                        <button type="button" wire:click="closeModal" class="btn btn-secondary">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            {{ $editing ? 'Actualizar' : 'Crear Plan' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @push('scripts')
    <script>
        window.addEventListener('swal', e => {
            Swal.fire({ title: e.detail[0]?.title ?? e.detail?.title, text: e.detail[0]?.text ?? e.detail?.text, icon: e.detail[0]?.icon ?? e.detail?.icon, timer: 2000, showConfirmButton: false });
        });
    </script>
    @endpush
