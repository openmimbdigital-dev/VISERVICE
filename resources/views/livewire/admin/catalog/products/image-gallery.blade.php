<div>
    <div
        x-data="{ dragging: false }"
        x-on:dragover.prevent="dragging = true"
        x-on:dragleave.prevent="dragging = false"
        x-on:drop.prevent="dragging = false; $refs.file_input.files = $event.dataTransfer.files; $refs.file_input.dispatchEvent(new Event('change'))"
        :class="dragging ? 'border-indigo-400 bg-indigo-50/60' : 'border-slate-200 bg-slate-50/60'"
        class="relative flex flex-col items-center justify-center gap-2 rounded-xl border-2 border-dashed px-4 py-6 text-center transition"
    >
        <svg class="h-8 w-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M12 12v9m0-9l-3 3m3-3l3 3"/>
        </svg>
        <p class="text-sm text-slate-600">
            Arrastra imágenes aquí o
            <label class="cursor-pointer font-semibold text-indigo-600 hover:text-indigo-700">
                selecciónalas
                <input x-ref="file_input" type="file" wire:model="new_images" multiple accept="image/png,image/jpeg,image/webp" class="hidden">
            </label>
        </p>
        <p class="text-xs text-slate-400">JPG, PNG o WebP — hasta 4 MB por imagen, máximo 10 a la vez</p>

        <div wire:loading wire:target="new_images" class="absolute inset-0 flex items-center justify-center rounded-xl bg-white/80">
            <span class="flex items-center gap-2 text-sm font-medium text-indigo-600">
                <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                Subiendo...
            </span>
        </div>
    </div>
    @error('new_images') <p class="mt-2 text-xs text-rose-600">{{ $message }}</p> @enderror
    @error('new_images.*') <p class="mt-2 text-xs text-rose-600">{{ $message }}</p> @enderror

    @if($images->isEmpty())
        <p class="mt-4 py-6 text-center text-sm text-slate-400">Este producto aún no tiene imágenes.</p>
    @else
        <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
            @foreach($images as $index => $image)
            <div wire:key="product-image-{{ $image->id }}" class="group relative aspect-square overflow-hidden rounded-xl border border-slate-200 bg-slate-100">
                <img src="{{ $image->url }}" alt="Imagen de {{ $product->name }}" class="h-full w-full object-cover">

                @if($image->is_primary)
                <span class="absolute left-1.5 top-1.5 inline-flex items-center gap-1 rounded-full bg-indigo-600 px-2 py-0.5 text-[10px] font-semibold text-white shadow">
                    <svg class="h-2.5 w-2.5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.957a1 1 0 00.95.69h4.162c.969 0 1.371 1.24.588 1.81l-3.368 2.447a1 1 0 00-.363 1.118l1.286 3.957c.3.921-.755 1.688-1.538 1.118l-3.368-2.447a1 1 0 00-1.176 0l-3.368 2.447c-.783.57-1.838-.197-1.538-1.118l1.286-3.957a1 1 0 00-.363-1.118L2.063 9.384c-.783-.57-.38-1.81.588-1.81h4.162a1 1 0 00.95-.69l1.286-3.957z"/></svg>
                    Principal
                </span>
                @endif

                <div class="absolute inset-0 flex flex-col items-center justify-center gap-1.5 bg-slate-900/0 opacity-0 transition group-hover:bg-slate-900/50 group-hover:opacity-100">
                    @unless($image->is_primary)
                    <button type="button" wire:click="setPrimary({{ $image->id }})" wire:loading.attr="disabled"
                        class="rounded-lg bg-white/95 px-2.5 py-1 text-[11px] font-semibold text-slate-700 shadow hover:bg-white">
                        Marcar principal
                    </button>
                    @endunless

                    <div class="flex items-center gap-1.5">
                        @if($index > 0)
                        <button type="button" wire:click="moveImage({{ $image->id }}, 'left')" title="Mover antes"
                            class="rounded-lg bg-white/95 p-1.5 text-slate-700 shadow hover:bg-white">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        </button>
                        @endif
                        <button type="button" wire:click="deleteImage({{ $image->id }})" wire:confirm="¿Eliminar esta imagen?" title="Eliminar"
                            class="rounded-lg bg-white/95 p-1.5 text-rose-600 shadow hover:bg-white">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M4 7h16M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3"/></svg>
                        </button>
                        @if($index < $images->count() - 1)
                        <button type="button" wire:click="moveImage({{ $image->id }}, 'right')" title="Mover después"
                            class="rounded-lg bg-white/95 p-1.5 text-slate-700 shadow hover:bg-white">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </button>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    @endif
</div>
