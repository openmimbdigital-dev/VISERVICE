@props([
    'links',
    'wirePrefix' => 'form.attribute_values',
])

@php
    use App\Enums\AttributeType;
@endphp

@if($links->isNotEmpty())
    <section {{ $attributes->merge(['class' => 'md:col-span-2 overflow-hidden rounded-2xl border border-slate-200/90 bg-slate-50/40']) }}>
        <div class="border-b border-slate-100 bg-slate-50/80 px-4 py-3 sm:px-5">
            <h3 class="text-sm font-semibold text-slate-800">Atributos del tipo de equipo</h3>
            <p class="mt-0.5 text-xs text-slate-500">Campos configurados según el tipo seleccionado.</p>
        </div>

        <div class="grid grid-cols-1 gap-5 p-4 sm:p-5 md:grid-cols-2">
            @foreach($links as $link)
                @php
                    $attribute = $link->attribute;
                    $field_key = $attribute->id;
                    $model_key = "{$wirePrefix}.{$field_key}";
                    $required  = $attribute->required;
                    $input_class = 'w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20';
                @endphp

                <div @class(['md:col-span-2' => in_array($attribute->type, [AttributeType::TEXTAREA, AttributeType::CHECKBOX, AttributeType::RADIO], true)])>
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">
                        {{ $attribute->name }}
                        @if($required)
                            <span class="text-rose-500">*</span>
                        @endif
                    </label>

                    @switch($attribute->type)
                        @case(AttributeType::TEXT)
                            <input type="text" wire:model="{{ $model_key }}"
                                class="{{ $input_class }} @error($model_key) border-rose-400 bg-rose-50 @enderror">
                            @break

                        @case(AttributeType::NUMBER)
                            <input type="number"
                                wire:model="{{ $model_key }}"
                                @if($attribute->min_value !== null) min="{{ $attribute->min_value }}" @endif
                                @if($attribute->max_value !== null) max="{{ $attribute->max_value }}" @endif
                                class="{{ $input_class }} @error($model_key) border-rose-400 bg-rose-50 @enderror">
                            @break

                        @case(AttributeType::TEXTAREA)
                            <textarea wire:model="{{ $model_key }}" rows="3"
                                class="{{ $input_class }} @error($model_key) border-rose-400 bg-rose-50 @enderror"></textarea>
                            @break

                        @case(AttributeType::SELECT)
                            <select wire:model="{{ $model_key }}"
                                class="{{ $input_class }} @error($model_key) border-rose-400 bg-rose-50 @enderror">
                                <option value="">Seleccionar</option>
                                @foreach($attribute->options ?? [] as $option)
                                    <option value="{{ $option['value'] ?? $option['label'] }}">{{ $option['label'] }}</option>
                                @endforeach
                            </select>
                            @break

                        @case(AttributeType::RADIO)
                            <div class="flex flex-wrap gap-3">
                                @foreach($attribute->options ?? [] as $option)
                                    @php $option_value = $option['value'] ?? $option['label']; @endphp
                                    <label class="inline-flex cursor-pointer items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700">
                                        <input type="radio" wire:model="{{ $model_key }}" value="{{ $option_value }}"
                                            class="border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                        {{ $option['label'] }}
                                    </label>
                                @endforeach
                            </div>
                            @break

                        @case(AttributeType::CHECKBOX)
                            <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                                @foreach($attribute->options ?? [] as $option)
                                    @php $option_value = $option['value'] ?? $option['label']; @endphp
                                    <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 bg-white px-3.5 py-2.5">
                                        <input type="checkbox" value="{{ $option_value }}" wire:model="{{ $model_key }}"
                                            class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                        <span class="text-sm text-slate-700">{{ $option['label'] }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @break

                        @case(AttributeType::COLOR)
                            <div class="flex items-center gap-3">
                                <input type="color" wire:model="{{ $model_key }}"
                                    class="h-10 w-14 cursor-pointer rounded-lg border border-slate-200 bg-white p-1">
                                <input type="text" wire:model="{{ $model_key }}"
                                    class="{{ $input_class }} max-w-[8rem] font-mono text-xs uppercase @error($model_key) border-rose-400 bg-rose-50 @enderror">
                            </div>
                            @break
                    @endswitch

                    @error($model_key)
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
            @endforeach
        </div>
    </section>
@endif
