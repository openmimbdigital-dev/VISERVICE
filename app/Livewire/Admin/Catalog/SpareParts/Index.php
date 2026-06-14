<?php

namespace App\Livewire\Admin\Catalog\SpareParts;

use App\Models\SparePartCatalog;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Catálogo de Repuestos')]
class Index extends Component
{
    public bool  $showModal  = false;
    public ?int  $editing_id = null;

    public string $code        = '';
    public string $name        = '';
    public string $description = '';
    public string $category    = '';
    public string $brand       = '';
    public string $unit        = 'und';
    public string $unit_price  = '0';
    public string $stock       = '0';
    public string $min_stock   = '0';
    public bool   $is_active   = true;

    protected function rules(): array
    {
        return [
            'name'        => 'required|string|max:150',
            'code'        => 'nullable|string|max:30',
            'description' => 'nullable|string',
            'category'    => 'nullable|string|max:60',
            'brand'       => 'nullable|string|max:60',
            'unit'        => 'required|string|max:20',
            'unit_price'  => 'required|numeric|min:0',
            'stock'       => 'required|integer|min:0',
            'min_stock'   => 'required|integer|min:0',
            'is_active'   => 'boolean',
        ];
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    #[On('open-spare-part-edit')]
    public function openEdit(int $id): void
    {
        $p = SparePartCatalog::findOrFail($id);
        $this->editing_id  = $p->id;
        $this->code        = $p->code ?? '';
        $this->name        = $p->name;
        $this->description = $p->description ?? '';
        $this->category    = $p->category ?? '';
        $this->brand       = $p->brand ?? '';
        $this->unit        = $p->unit;
        $this->unit_price  = $p->unit_price;
        $this->stock       = $p->stock;
        $this->min_stock   = $p->min_stock;
        $this->is_active   = $p->is_active;
        $this->showModal   = true;
    }

    public function save(): void
    {
        $this->validate();
        $data = [
            'business_id' => auth()->user()->business_id,
            'code'        => $this->code ?: null,
            'name'        => $this->name,
            'description' => $this->description ?: null,
            'category'    => $this->category ?: null,
            'brand'       => $this->brand ?: null,
            'unit'        => $this->unit,
            'unit_price'  => $this->unit_price,
            'stock'       => (int) $this->stock,
            'min_stock'   => (int) $this->min_stock,
            'is_active'   => $this->is_active,
            'created_by'  => auth()->id(),
        ];

        if ($this->editing_id) {
            SparePartCatalog::findOrFail($this->editing_id)->update($data);
            $this->dispatch('swal', ['title' => 'Repuesto actualizado', 'icon' => 'success']);
        } else {
            SparePartCatalog::create($data);
            $this->dispatch('swal', ['title' => 'Repuesto creado', 'icon' => 'success']);
        }
        $this->closeModal();
    }

    #[On('spare-part-deleted')]
    public function onRecordDeleted(): void {}

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->editing_id = null;
        $this->code = $this->name = $this->description = $this->category = $this->brand = '';
        $this->unit       = 'und';
        $this->unit_price = $this->stock = $this->min_stock = '0';
        $this->is_active  = true;
        $this->resetValidation();
    }

    public function render()
    {
        $business_id = auth()->user()->business_id;

        $categories = SparePartCatalog::where('business_id', $business_id)
            ->whereNotNull('category')->distinct()->pluck('category');

        $stats = [
            'total'     => SparePartCatalog::where('business_id', $business_id)->count(),
            'low_stock' => SparePartCatalog::where('business_id', $business_id)
                ->whereColumn('stock', '<=', 'min_stock')->where('is_active', true)->count(),
        ];

        return view('livewire.admin.catalog.spare-parts.index', compact('categories', 'stats'));
    }
}
