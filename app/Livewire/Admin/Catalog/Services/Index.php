<?php

namespace App\Livewire\Admin\Catalog\Services;

use App\Models\ServiceCatalog;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Catálogo de Servicios')]
class Index extends Component
{
    public bool  $showModal  = false;
    public ?int  $editing_id = null;

    public string $code             = '';
    public string $name             = '';
    public string $description      = '';
    public string $category         = '';
    public string $default_price    = '0';
    public string $duration_minutes = '60';
    public bool   $is_active        = true;

    protected function rules(): array
    {
        return [
            'name'             => 'required|string|max:150',
            'code'             => 'nullable|string|max:30',
            'description'      => 'nullable|string',
            'category'         => 'nullable|string|max:60',
            'default_price'    => 'required|numeric|min:0',
            'duration_minutes' => 'required|integer|min:1',
            'is_active'        => 'boolean',
        ];
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    #[On('open-service-edit')]
    public function openEdit(int $id): void
    {
        $s = ServiceCatalog::findOrFail($id);
        $this->editing_id       = $s->id;
        $this->code             = $s->code ?? '';
        $this->name             = $s->name;
        $this->description      = $s->description ?? '';
        $this->category         = $s->category ?? '';
        $this->default_price    = $s->default_price;
        $this->duration_minutes = $s->duration_minutes;
        $this->is_active        = $s->is_active;
        $this->showModal        = true;
    }

    public function save(): void
    {
        $this->validate();
        $data = [
            'business_id'      => auth()->user()->business_id,
            'code'             => $this->code ?: null,
            'name'             => $this->name,
            'description'      => $this->description ?: null,
            'category'         => $this->category ?: null,
            'default_price'    => $this->default_price,
            'duration_minutes' => (int) $this->duration_minutes,
            'is_active'        => $this->is_active,
            'created_by'       => auth()->id(),
        ];

        if ($this->editing_id) {
            ServiceCatalog::findOrFail($this->editing_id)->update($data);
            $this->dispatch('swal', ['title' => 'Servicio actualizado', 'icon' => 'success']);
        } else {
            ServiceCatalog::create($data);
            $this->dispatch('swal', ['title' => 'Servicio creado', 'icon' => 'success']);
        }
        $this->closeModal();
    }

    #[On('service-deleted')]
    public function onRecordDeleted(): void {}

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->editing_id = null;
        $this->code = $this->name = $this->description = $this->category = '';
        $this->default_price    = '0';
        $this->duration_minutes = '60';
        $this->is_active        = true;
        $this->resetValidation();
    }

    public function render()
    {
        $business_id = auth()->user()->business_id;

        $categories = ServiceCatalog::where('business_id', $business_id)
            ->whereNotNull('category')->distinct()->pluck('category');

        $stats = [
            'total'  => ServiceCatalog::where('business_id', $business_id)->count(),
            'active' => ServiceCatalog::where('business_id', $business_id)->where('is_active', true)->count(),
        ];

        return view('livewire.admin.catalog.services.index', compact('categories', 'stats'));
    }
}
