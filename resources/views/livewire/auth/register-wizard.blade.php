<div class="min-h-screen flex">

    {{-- Panel izquierdo — Branding + Pasos --}}
    <div class="hidden lg:flex lg:w-2/5 xl:w-1/3 relative overflow-hidden bg-gradient-to-br from-slate-900 via-indigo-950 to-slate-900 flex-col p-10">
        <div class="absolute inset-0 grid-dot opacity-60"></div>
        <div class="absolute -top-40 -left-40 w-96 h-96 rounded-full bg-indigo-600/20 blur-3xl"></div>
        <div class="absolute -bottom-32 -right-20 w-80 h-80 rounded-full bg-indigo-700/25 blur-3xl"></div>

        {{-- Logo --}}
        <div class="relative z-10 flex items-center gap-3 mb-12">
            <img src="{{ asset('images/logo-initial.png') }}" alt="VISERVICE" class="h-10 w-auto drop-shadow-xl select-none">
            <span class="text-2xl font-extrabold text-white tracking-tight">VIS<span class="text-indigo-400">ERVICE</span></span>
        </div>

        {{-- Pasos --}}
        <div class="relative z-10 flex-1">
            <h2 class="text-lg font-bold text-white mb-1">Crear tu cuenta</h2>
            <p class="text-slate-400 text-sm mb-8">Sigue los pasos para registrar tu comercio.</p>

            <div class="space-y-5">
                @foreach([
                    [1, 'Datos del Comercio',  'Nombre, NIT y tipo de negocio'],
                    [2, 'Datos Personales',    'Tu usuario y contraseña'],
                    [3, 'Selección de Plan',   'Elige el plan que más te conviene'],
                    [4, 'Método de Pago',      'Transferencia o efectivo'],
                ] as [$num, $label, $desc])
                    @php $isDone = $step > $num; $isCurrent = $step === $num; @endphp
                    <div class="flex items-start gap-4">
                        <div @class([
                            'w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold shrink-0 transition-all',
                            'bg-emerald-500 text-white'  => $isDone,
                            'bg-indigo-500 text-white ring-4 ring-indigo-500/30' => $isCurrent,
                            'bg-white/10 text-white/30'  => !$isDone && !$isCurrent,
                        ])>
                            @if($isDone)
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            @else
                                {{ $num }}
                            @endif
                        </div>
                        <div class="pt-1">
                            <p @class(['text-sm font-semibold', 'text-white' => $isCurrent || $isDone, 'text-white/30' => !$isCurrent && !$isDone])>{{ $label }}</p>
                            <p @class(['text-xs mt-0.5', 'text-slate-400' => $isCurrent, 'text-emerald-400' => $isDone, 'text-white/20' => !$isCurrent && !$isDone])>{{ $desc }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <p class="relative z-10 text-xs text-slate-600 mt-8">© {{ date('Y') }} VISERVICE · Todos los derechos reservados</p>
    </div>

    {{-- Panel derecho — Formulario --}}
    <div class="flex-1 flex items-center justify-center p-6 sm:p-10 bg-white overflow-y-auto">
        <div class="w-full max-w-lg">

            {{-- Breadcrumb mobile --}}
            <div class="flex items-center gap-2 mb-6 lg:hidden">
                <img src="{{ asset('images/logo-initial.png') }}" alt="VISERVICE" class="h-8 w-auto">
                <span class="text-base font-bold text-slate-800">VISERVICE</span>
                <span class="text-slate-300 mx-1">·</span>
                <span class="text-sm text-slate-500">Paso {{ $step }} de {{ $totalSteps }}</span>
            </div>

            {{-- Progreso móvil --}}
            <div class="mb-6 lg:hidden">
                <div class="flex gap-1.5">
                    @for($i = 1; $i <= $totalSteps; $i++)
                        <div @class(['h-1.5 flex-1 rounded-full transition-all', 'bg-indigo-600' => $i <= $step, 'bg-slate-200' => $i > $step])></div>
                    @endfor
                </div>
            </div>

            {{-- ── PASO 1: Datos del Comercio ───────────────────────────────── --}}
            @if($step === 1)
            <div>
                <div class="mb-7">
                    <h2 class="text-2xl font-bold text-slate-900">Datos del Comercio</h2>
                    <p class="mt-1 text-sm text-slate-500">Información de tu negocio para crear la cuenta.</p>
                </div>

                <div class="space-y-4">
                    {{-- Nombre --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Nombre del comercio <span class="text-red-500">*</span></label>
                        <input wire:model="business_name" type="text" placeholder="Ej: Taller AutoService Norte"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 py-3 px-4 text-sm text-slate-900 placeholder-slate-400 focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition @error('business_name') border-red-400 bg-red-50 @enderror">
                        @error('business_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    {{-- Tipo de negocio + NIT --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Tipo de organización <span class="text-red-500">*</span></label>
                            <select wire:model="organization_type_id"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 py-3 px-4 text-sm text-slate-900 focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition @error('organization_type_id') border-red-400 @enderror">
                                <option value="">Seleccionar...</option>
                                @foreach($business_types as $business_type)
                                    @php
                                        $options = $organization_types->where('business_type_id', $business_type->id);
                                    @endphp
                                    @if($options->isNotEmpty())
                                        <optgroup label="{{ $business_type->name }}">
                                            @foreach($options as $type)
                                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                                            @endforeach
                                        </optgroup>
                                    @endif
                                @endforeach
                            </select>
                            @error('organization_type_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">NIT / RUT <span class="text-red-500">*</span></label>
                            <input wire:model="business_nit" type="text" placeholder="900.123.456-7"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 py-3 px-4 text-sm text-slate-900 placeholder-slate-400 focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition @error('business_nit') border-red-400 bg-red-50 @enderror">
                            @error('business_nit') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- Teléfono + Email --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Teléfono <span class="text-red-500">*</span></label>
                            <input wire:model="business_phone" type="tel" placeholder="+57 300 123 4567"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 py-3 px-4 text-sm text-slate-900 placeholder-slate-400 focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition @error('business_phone') border-red-400 bg-red-50 @enderror">
                            @error('business_phone') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Correo del comercio</label>
                            <input wire:model="business_email" type="email" placeholder="info@micomercio.com"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 py-3 px-4 text-sm text-slate-900 placeholder-slate-400 focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition @error('business_email') border-red-400 bg-red-50 @enderror">
                            @error('business_email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- Dirección --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Dirección</label>
                        <input wire:model="business_address" type="text" placeholder="Cra 15 # 45-67, Barrio Centro"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 py-3 px-4 text-sm text-slate-900 placeholder-slate-400 focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition">
                    </div>

                    {{-- Ciudad --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Ciudad</label>
                        <select wire:model="business_city_id"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 py-3 px-4 text-sm text-slate-900 focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition">
                            <option value="">Seleccionar ciudad...</option>
                            @foreach($cities as $city)
                                <option value="{{ $city->id }}">{{ $city->name }}{{ $city->state_province ? ' — ' . $city->state_province : '' }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Logo del comercio --}}
                    <div x-data="{ preview: null }">
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">
                            Logo del comercio
                            <span class="text-slate-400 font-normal">(opcional — puedes subirlo después)</span>
                        </label>
                        <div class="flex items-start gap-4">
                            {{-- Preview / placeholder --}}
                            <div class="shrink-0 w-20 h-20 rounded-xl border-2 border-dashed border-slate-200 bg-slate-50 flex items-center justify-center overflow-hidden">
                                <template x-if="preview">
                                    <img :src="preview" class="w-full h-full object-cover rounded-xl">
                                </template>
                                <template x-if="!preview">
                                    <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </template>
                            </div>
                            <div class="flex-1">
                                <input wire:model="business_logo" type="file" accept="image/jpg,image/jpeg,image/png,image/webp"
                                    x-on:change="
                                        const file = $event.target.files[0];
                                        if (file) { const reader = new FileReader(); reader.onload = e => preview = e.target.result; reader.readAsDataURL(file); }
                                        else { preview = null; }
                                    "
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 px-4 text-sm text-slate-900 file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-100 file:text-indigo-700 hover:file:bg-indigo-200 cursor-pointer transition @error('business_logo') border-red-400 bg-red-50 @enderror">
                                <p class="mt-1.5 text-xs text-slate-400">JPG, PNG o WebP · máximo 2 MB</p>
                                @error('business_logo') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end mt-8">
                    <button wire:click="nextStep" type="button"
                        class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-6 py-3 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 active:scale-[0.98] focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all">
                        Continuar
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </div>
            @endif

            {{-- ── PASO 2: Datos del Usuario ────────────────────────────────── --}}
            @if($step === 2)
            <div>
                <div class="mb-7">
                    <h2 class="text-2xl font-bold text-slate-900">Datos Personales</h2>
                    <p class="mt-1 text-sm text-slate-500">Crea el usuario administrador de <strong>{{ $business_name }}</strong>.</p>
                </div>

                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Nombre <span class="text-red-500">*</span></label>
                            <input wire:model="first_name" type="text" placeholder="Juan"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 py-3 px-4 text-sm text-slate-900 placeholder-slate-400 focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition @error('first_name') border-red-400 bg-red-50 @enderror">
                            @error('first_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Apellido <span class="text-red-500">*</span></label>
                            <input wire:model="last_name" type="text" placeholder="Pérez"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 py-3 px-4 text-sm text-slate-900 placeholder-slate-400 focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition @error('last_name') border-red-400 bg-red-50 @enderror">
                            @error('last_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Correo electrónico <span class="text-red-500">*</span></label>
                        <input wire:model="email" type="email" placeholder="juan@micomercio.com"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 py-3 px-4 text-sm text-slate-900 placeholder-slate-400 focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition @error('email') border-red-400 bg-red-50 @enderror">
                        @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Usuario <span class="text-red-500">*</span></label>
                            <input wire:model="username" type="text" placeholder="juanperez"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 py-3 px-4 text-sm text-slate-900 placeholder-slate-400 focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition @error('username') border-red-400 bg-red-50 @enderror">
                            @error('username') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Teléfono</label>
                            <input wire:model="user_phone" type="tel" placeholder="+57 300 000 0000"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 py-3 px-4 text-sm text-slate-900 placeholder-slate-400 focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition">
                        </div>
                    </div>

                    <div x-data="{ show: false }">
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Contraseña <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input wire:model="password" :type="show ? 'text' : 'password'" placeholder="Mínimo 8 caracteres"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 py-3 pl-4 pr-10 text-sm text-slate-900 placeholder-slate-400 focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition @error('password') border-red-400 bg-red-50 @enderror">
                            <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 hover:text-slate-600">
                                <svg x-show="!show" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <svg x-show="show" class="h-4 w-4" style="display:none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                            </button>
                        </div>
                        @error('password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Confirmar contraseña <span class="text-red-500">*</span></label>
                        <input wire:model="password_confirmation" type="password" placeholder="Repite tu contraseña"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 py-3 px-4 text-sm text-slate-900 placeholder-slate-400 focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition @error('password_confirmation') border-red-400 bg-red-50 @enderror">
                        @error('password_confirmation') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="flex justify-between mt-8">
                    <button wire:click="prevStep" type="button"
                        class="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-5 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50 transition">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        Atrás
                    </button>
                    <button wire:click="nextStep" type="button"
                        class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-6 py-3 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 active:scale-[0.98] transition-all">
                        Continuar
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </div>
            @endif

            {{-- ── PASO 3: Selección de Plan ────────────────────────────────── --}}
            @if($step === 3)
            <div>
                <div class="mb-6">
                    <h2 class="text-2xl font-bold text-slate-900">Selección de Plan</h2>
                    <p class="mt-1 text-sm text-slate-500">Elige el plan que mejor se adapte a tu comercio.</p>
                </div>

                {{-- Ciclo de facturación --}}
                <div class="mb-5">
                    <p class="text-sm font-medium text-slate-700 mb-2">Ciclo de facturación</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach([
                            'monthly'    => ['Mensual',    ''],
                            'quarterly'  => ['Trimestral', '5% dto'],
                            'semiannual' => ['Semestral',  '10% dto'],
                            'annual'     => ['Anual',      '20% dto'],
                        ] as $cycle => [$label, $badge])
                        <button wire:click="$set('billing_cycle', '{{ $cycle }}')" type="button"
                            @class([
                                'px-4 py-2 rounded-lg text-sm font-medium border transition-all flex items-center gap-2',
                                'bg-indigo-600 border-indigo-600 text-white shadow-sm' => $billing_cycle === $cycle,
                                'bg-white border-slate-200 text-slate-700 hover:border-indigo-300 hover:bg-indigo-50' => $billing_cycle !== $cycle,
                            ])>
                            {{ $label }}
                            @if($badge)
                                <span @class(['text-xs px-1.5 py-0.5 rounded-full', 'bg-white/20 text-white' => $billing_cycle === $cycle, 'bg-emerald-100 text-emerald-700' => $billing_cycle !== $cycle])>{{ $badge }}</span>
                            @endif
                        </button>
                        @endforeach
                    </div>
                </div>

                @error('plan_id') <p class="mb-3 text-sm text-red-600 font-medium">{{ $message }}</p> @enderror

                {{-- Tarjetas de planes --}}
                <div class="space-y-3">
                    @foreach($plans as $plan)
                        @php $pData = $plan->getPriceForCycle($billing_cycle); @endphp
                        <button wire:click="$set('plan_id', {{ $plan->id }})" type="button"
                            @class([
                                'w-full text-left rounded-2xl border-2 p-4 transition-all hover:border-indigo-400',
                                'border-indigo-500 bg-indigo-50 ring-2 ring-indigo-500/20' => $plan_id === $plan->id,
                                'border-slate-200 bg-white hover:bg-slate-50' => $plan_id !== $plan->id,
                            ])>
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="font-semibold text-slate-900">{{ $plan->name }}</span>
                                        @if($plan_id === $plan->id)
                                            <span class="inline-flex items-center gap-1 text-xs font-medium text-indigo-700 bg-indigo-100 px-2 py-0.5 rounded-full">
                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                                Seleccionado
                                            </span>
                                        @endif
                                    </div>
                                    @if($plan->description)
                                        <p class="text-xs text-slate-500 mb-2">{{ $plan->description }}</p>
                                    @endif
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach(array_slice((array)$plan->features, 0, 4) as $feature)
                                            <span class="text-xs text-slate-600 bg-slate-100 px-2 py-0.5 rounded-full">{{ $feature }}</span>
                                        @endforeach
                                        @if(count((array)$plan->features) > 4)
                                            <span class="text-xs text-indigo-600 font-medium">+{{ count((array)$plan->features) - 4 }} más</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="text-right shrink-0">
                                    <p class="text-xl font-bold text-slate-900">${{ number_format($pData['total'], 0, ',', '.') }}</p>
                                    <p class="text-xs text-slate-500">por {{ $pData['months'] }} {{ $pData['months'] == 1 ? 'mes' : 'meses' }}</p>
                                    @if($pData['discount'] > 0)
                                        <p class="text-xs text-emerald-600 font-medium mt-0.5">{{ $pData['discount'] }}% descuento</p>
                                    @endif
                                </div>
                            </div>
                        </button>
                    @endforeach
                </div>

                @if($selected_plan && $price_data)
                <div class="mt-4 rounded-xl bg-indigo-50 border border-indigo-100 px-4 py-3 text-sm">
                    <p class="font-medium text-indigo-900">Resumen: <span class="font-normal text-indigo-700">{{ $selected_plan->name }} — Ciclo {{ ['monthly'=>'Mensual','quarterly'=>'Trimestral','semiannual'=>'Semestral','annual'=>'Anual'][$billing_cycle] }}</span></p>
                    <p class="text-indigo-700 mt-0.5">Total a pagar: <strong>${{ number_format($price_data['total'], 0, ',', '.') }} COP</strong></p>
                </div>
                @endif

                <div class="flex justify-between mt-8">
                    <button wire:click="prevStep" type="button"
                        class="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-5 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50 transition">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        Atrás
                    </button>
                    <button wire:click="nextStep" type="button"
                        class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-6 py-3 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 active:scale-[0.98] transition-all">
                        Continuar
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </div>
            @endif

            {{-- ── PASO 4: Método de Pago ───────────────────────────────────── --}}
            @if($step === 4)
            <div>
                <div class="mb-6">
                    <h2 class="text-2xl font-bold text-slate-900">Método de Pago</h2>
                    @if($selected_plan && $price_data)
                        <p class="mt-1 text-sm text-slate-500">
                            Total a pagar: <strong class="text-slate-900">${{ number_format($price_data['total'], 0, ',', '.') }} COP</strong>
                            · {{ $selected_plan->name }}
                        </p>
                    @endif
                </div>

                @error('payment_type') <p class="mb-4 text-sm text-red-600 font-medium">{{ $message }}</p> @enderror

                {{-- Opciones de pago --}}
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <button wire:click="$set('payment_type', 'transfer')" type="button"
                        @class([
                            'rounded-2xl border-2 p-5 text-left transition-all',
                            'border-indigo-500 bg-indigo-50 ring-2 ring-indigo-500/20' => $payment_type === 'transfer',
                            'border-slate-200 bg-white hover:border-indigo-300 hover:bg-slate-50' => $payment_type !== 'transfer',
                        ])>
                        <div class="w-10 h-10 rounded-xl bg-indigo-100 flex items-center justify-center mb-3">
                            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/></svg>
                        </div>
                        <p class="font-semibold text-slate-900 text-sm">Transferencia bancaria</p>
                        <p class="text-xs text-slate-500 mt-1">Envía el comprobante y lo verificamos.</p>
                    </button>

                    <button wire:click="$set('payment_type', 'cash')" type="button"
                        @class([
                            'rounded-2xl border-2 p-5 text-left transition-all',
                            'border-indigo-500 bg-indigo-50 ring-2 ring-indigo-500/20' => $payment_type === 'cash',
                            'border-slate-200 bg-white hover:border-indigo-300 hover:bg-slate-50' => $payment_type !== 'cash',
                        ])>
                        <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center mb-3">
                            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        </div>
                        <p class="font-semibold text-slate-900 text-sm">Efectivo</p>
                        <p class="text-xs text-slate-500 mt-1">Paga en persona en nuestras oficinas.</p>
                    </button>
                </div>

                {{-- Sección Transferencia --}}
                @if($payment_type === 'transfer')
                <div class="space-y-4">
                    <div class="rounded-2xl border border-slate-200 divide-y divide-slate-100 overflow-hidden">
                        <div class="px-4 py-3 bg-slate-50">
                            <p class="text-sm font-semibold text-slate-700">Cuentas bancarias para transferencia</p>
                        </div>
                        @foreach($bank_accounts as $account)
                        <div class="px-4 py-3.5 flex items-center justify-between gap-4">
                            <div>
                                <p class="text-sm font-semibold text-slate-900">{{ $account->bank_name }}</p>
                                <p class="text-xs text-slate-500 mt-0.5">{{ $account->account_type_label }} · {{ $account->account_number }}</p>
                                <p class="text-xs text-slate-500">{{ $account->account_holder }} · {{ $account->document_type }}: {{ $account->document_number }}</p>
                            </div>
                            <div class="shrink-0">
                                <span class="inline-block text-xs font-medium bg-slate-100 text-slate-600 px-2.5 py-1 rounded-lg">{{ strtoupper($account->account_type) }}</span>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">
                            Comprobante de transferencia <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input wire:model="payment_proof" type="file" accept=".jpg,.jpeg,.png,.pdf"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 py-3 px-4 text-sm text-slate-900 file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-100 file:text-indigo-700 hover:file:bg-indigo-200 cursor-pointer transition @error('payment_proof') border-red-400 bg-red-50 @enderror">
                        </div>
                        <p class="mt-1 text-xs text-slate-400">JPG, PNG o PDF · máximo 5 MB</p>
                        @error('payment_proof') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Número de referencia (opcional)</label>
                        <input wire:model="payment_reference" type="text" placeholder="Número de transacción o referencia del banco"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 py-3 px-4 text-sm text-slate-900 placeholder-slate-400 focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition">
                    </div>
                </div>
                @endif

                {{-- Sección Efectivo --}}
                @if($payment_type === 'cash')
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-emerald-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <div>
                            <p class="text-sm font-semibold text-emerald-900">Pago en efectivo</p>
                            <p class="text-sm text-emerald-700 mt-1">
                                Registraremos tu solicitud. Un asesor se pondrá en contacto contigo para coordinar el pago en persona.
                                Una vez confirmado, activaremos tu cuenta.
                            </p>
                        </div>
                    </div>
                </div>
                @endif

                <div class="flex justify-between mt-8">
                    <button wire:click="prevStep" type="button"
                        class="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-5 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50 transition">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        Atrás
                    </button>
                    <button wire:click="submit" type="button" wire:loading.attr="disabled"
                        class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-6 py-3 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 active:scale-[0.98] disabled:opacity-60 transition-all">
                        <span wire:loading.remove wire:target="submit">
                            Finalizar registro
                        </span>
                        <span wire:loading wire:target="submit" class="inline-flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/></svg>
                            Procesando...
                        </span>
                        <svg wire:loading.remove wire:target="submit" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </button>
                </div>
            </div>
            @endif

            {{-- Link volver al login --}}
            <p class="mt-8 text-center text-sm text-slate-500">
                ¿Ya tienes cuenta?
                <a href="{{ route('login') }}" class="font-semibold text-indigo-600 hover:text-indigo-700 transition">Iniciar sesión</a>
            </p>

        </div>
    </div>
</div>
