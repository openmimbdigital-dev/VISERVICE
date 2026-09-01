@props(['sections' => []])

@foreach($sections as $section)
    @php $section_name = org_term($section['name']); @endphp
    @if($section['behavior'] === 'single_link')
        {{-- Enlace directo --}}
        <div x-cloak x-show="showSidebarIconsOnly()">
            <a href="{{ $section['url'] }}" wire:navigate
                class="flex w-full items-center justify-center rounded-lg px-2.5 py-2.5 text-sm font-medium transition
                    {{ $section['is_active'] ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}"
                title="{{ $section_name }}">
                <x-layout.sidebar-icon :path="$section['icon_svg_path']" :class="$section['icon_color_class']" size="md" />
            </a>
        </div>
        <div x-show="showSidebarLabels()" x-cloak>
            <a href="{{ $section['url'] }}" wire:navigate
                class="flex items-center gap-3 rounded-lg px-2.5 py-2.5 text-sm font-medium transition
                    {{ $section['is_active'] ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                <x-layout.sidebar-icon :path="$section['icon_svg_path']" :class="$section['icon_color_class']" size="md" />
                <span class="truncate">{{ $section_name }}</span>
            </a>
        </div>
    @else
        @php $slug = $section['slug']; @endphp
        {{-- Colapsado: popup --}}
        <div x-cloak x-show="showSidebarIconsOnly()" class="relative" @click.outside="if (collapsedOpen === '{{ $slug }}') closeCollapsedMenu()">
            <button type="button" @click.stop="toggleCollapsedMenu('{{ $slug }}')"
                class="flex w-full items-center justify-center rounded-lg px-2.5 py-2.5 text-sm font-medium transition
                    {{ $section['is_active'] ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}"
                title="{{ $section_name }}"
                :aria-expanded="collapsedOpen === '{{ $slug }}'">
                <x-layout.sidebar-icon :path="$section['icon_svg_path']" :class="$section['icon_color_class']" size="md" />
            </button>
            <div x-show="collapsedOpen === '{{ $slug }}'"
                x-transition:enter="transition ease-out duration-100"
                x-transition:enter-start="opacity-0 -translate-y-0.5"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-75"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="absolute left-0 right-0 top-full z-[100] mt-1 flex flex-col gap-0.5 rounded-xl border border-slate-700/90 bg-slate-900 p-1 shadow-lg ring-1 ring-white/5">
                @foreach($section['items'] as $item)
                <a href="{{ $item['url'] }}" wire:navigate @click="closeCollapsedMenu()"
                    class="flex items-center justify-center rounded-lg py-2.5 transition {{ $item['is_active'] ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}"
                    title="{{ org_term($item['name']) }}">
                    <x-layout.sidebar-icon
                        :path="$item['icon_svg_path'] ?? $section['icon_svg_path']"
                        :class="$item['icon_color_class'] ?? $section['icon_color_class']"
                        size="md" />
                </a>
                @endforeach
            </div>
        </div>

        {{-- Expandido: acordeón --}}
        <div x-show="showSidebarLabels()" x-cloak class="space-y-0.5">
            <button type="button" @click="toggleNav('{{ $slug }}')"
                class="w-full flex items-center gap-3 rounded-lg px-2.5 py-2.5 text-sm font-medium transition text-left
                    {{ $section['is_active'] ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                <x-layout.sidebar-icon :path="$section['icon_svg_path']" :class="$section['icon_color_class']" size="md" />
                <span class="truncate flex-1">{{ $section_name }}</span>
                <svg class="h-4 w-4 shrink-0 text-slate-400 transition-transform duration-200"
                    :class="isNavOpen('{{ $slug }}') ? 'rotate-180' : ''"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="isNavOpen('{{ $slug }}')" class="mt-0.5 space-y-0.5 border-l border-slate-700/80 ml-4 pl-3">
                @foreach($section['items'] as $item)
                <a href="{{ $item['url'] }}" wire:navigate
                    class="flex items-center gap-2 rounded-lg px-2.5 py-2 text-sm transition {{ $item['is_active'] ? 'bg-slate-800/90 text-white font-medium' : 'text-slate-400 hover:bg-slate-800/60 hover:text-white' }}"
                    title="{{ org_term($item['name']) }}">
                    <x-layout.sidebar-icon
                        :path="$item['icon_svg_path'] ?? $section['icon_svg_path']"
                        :class="$item['icon_color_class'] ?? $section['icon_color_class']"
                        size="sm" />
                    <span class="truncate">{{ org_term($item['name']) }}</span>
                    @if(! empty($item['badge']))
                    <span class="ml-auto shrink-0 inline-flex items-center justify-center h-5 min-w-5 px-1.5 rounded-full bg-amber-500 text-white text-[10px] font-bold">{{ $item['badge'] }}</span>
                    @endif
                </a>
                @endforeach
            </div>
        </div>
    @endif
@endforeach
