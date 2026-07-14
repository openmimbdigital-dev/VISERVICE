<?php

namespace App\Livewire\Admin\Businesses;

use App\Models\Business;
use App\Models\BusinessType;
use App\Models\MenuSection;
use App\Support\BusinessModuleAccess;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Módulos por negocio')]
class ModuleAccess extends Component
{
    public ?int $business_type_id = null;

    /** @var list<int|string> */
    public array $selected_business_ids = [];

    /** @var list<int|string> */
    public array $selected_section_ids = [];

    /** @var list<int|string> */
    public array $selected_menu_item_ids = [];

    public function mount(): void
    {
        abort_unless(
            auth()->user()?->hasRole('superAdmin') && auth()->user()?->can('businesses.manage_modules'),
            403
        );

        $first = BusinessType::query()->where('status', true)->orderBy('name')->first();
        $this->business_type_id = $first?->id;
    }

    public function updatedBusinessTypeId(): void
    {
        $this->selected_business_ids = [];
        $this->clearModuleSelection();
    }

    public function updatedSelectedBusinessIds(): void
    {
        $this->syncModuleSelectionFromBusinesses();
    }

    public function toggleSection(int $section_id): void
    {
        abort_unless(
            auth()->user()?->hasRole('superAdmin') && auth()->user()?->can('businesses.manage_modules'),
            403
        );

        $section = MenuSection::query()
            ->where('assignable_to_business', true)
            ->with(['items' => fn ($q) => $q->where('active', true)])
            ->find($section_id);

        if (! $section) {
            return;
        }

        $item_ids = $section->items->pluck('id')->map(fn ($id) => (string) $id)->all();
        $section_key = (string) $section_id;
        $all_selected = in_array($section_key, $this->selected_section_ids, true)
            && collect($item_ids)->every(fn ($id) => in_array($id, $this->selected_menu_item_ids, true));

        if ($all_selected) {
            $this->selected_section_ids = array_values(array_diff($this->selected_section_ids, [$section_key]));
            $this->selected_menu_item_ids = array_values(array_diff($this->selected_menu_item_ids, $item_ids));
        } else {
            $this->selected_section_ids = array_values(array_unique([...$this->selected_section_ids, $section_key]));
            $this->selected_menu_item_ids = array_values(array_unique([...$this->selected_menu_item_ids, ...$item_ids]));
        }
    }

    public function save(): void
    {
        abort_unless(
            auth()->user()?->hasRole('superAdmin') && auth()->user()?->can('businesses.manage_modules'),
            403
        );

        $this->validate([
            'business_type_id'        => 'required|exists:business_types,id',
            'selected_business_ids'   => 'required|array|min:1',
            'selected_business_ids.*' => [
                'integer',
                Rule::exists('businesses', 'id')->where(
                    fn ($q) => $q->where('business_type_id', $this->business_type_id)->whereNull('deleted_at')
                ),
            ],
            'selected_section_ids'    => 'array',
            'selected_menu_item_ids'  => 'array',
        ], [
            'selected_business_ids.required' => 'Selecciona al menos un negocio.',
        ]);

        $section_ids = array_map('intval', $this->selected_section_ids);
        $item_ids    = array_map('intval', $this->selected_menu_item_ids);

        $businesses = Business::query()
            ->whereIn('id', $this->selected_business_ids)
            ->whereNull('business_id')
            ->get();

        if ($businesses->isEmpty()) {
            $this->addError('selected_business_ids', 'Solo se pueden asignar módulos a negocios raíz (sin padre).');

            return;
        }

        foreach ($businesses as $business) {
            BusinessModuleAccess::syncBusinessModules($business, $section_ids, $item_ids);
        }

        $this->clearModuleSelection();
        $this->syncModuleSelectionFromBusinesses();

        $this->dispatch('swal', [
            'title' => "Módulos actualizados en {$businesses->count()} negocio(s).",
            'icon'  => 'success',
        ]);
    }

    private function syncModuleSelectionFromBusinesses(): void
    {
        $ids = array_values(array_filter(array_map('intval', $this->selected_business_ids)));

        if ($ids === []) {
            $this->clearModuleSelection();

            return;
        }

        $businesses = Business::query()
            ->with(['menuSections', 'menuItems'])
            ->where('business_type_id', $this->business_type_id)
            ->whereIn('id', $ids)
            ->get();

        if ($businesses->isEmpty()) {
            $this->clearModuleSelection();

            return;
        }

        $section_ids = $businesses->first()->menuSections->pluck('id');
        $item_ids    = $businesses->first()->menuItems->pluck('id');

        foreach ($businesses->skip(1) as $business) {
            $section_ids = $section_ids->intersect($business->menuSections->pluck('id'));
            $item_ids    = $item_ids->intersect($business->menuItems->pluck('id'));
        }

        $this->selected_section_ids = $section_ids->map(fn ($id) => (string) $id)->values()->all();
        $this->selected_menu_item_ids = $item_ids->map(fn ($id) => (string) $id)->values()->all();
    }

    private function clearModuleSelection(): void
    {
        $this->selected_section_ids   = [];
        $this->selected_menu_item_ids = [];
    }

    public function render()
    {
        $business_types = BusinessType::query()->where('status', true)->orderBy('name')->get();

        $assignable_sections = MenuSection::query()
            ->where('active', true)
            ->where('assignable_to_business', true)
            ->orderBy('sort_order')
            ->with(['items' => fn ($q) => $q->where('active', true)->orderBy('sort_order')])
            ->get();

        $businesses_for_type = $this->business_type_id
            ? Business::query()->where('business_type_id', $this->business_type_id)->whereNull('business_id')->orderBy('name')->get()
            : collect();

        $assigned_businesses = $this->business_type_id
            ? Business::query()
                ->where('business_type_id', $this->business_type_id)
                ->whereNull('business_id')
                ->with(['menuSections', 'menuItems'])
                ->orderBy('name')
                ->get()
            : collect();

        return view('livewire.admin.businesses.module-access', compact(
            'business_types',
            'assignable_sections',
            'businesses_for_type',
            'assigned_businesses'
        ));
    }
}
