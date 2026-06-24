<?php

namespace App\Livewire\Admin\Workshop\Equipment;

use App\Models\Client;
use App\Models\Equipment;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Equipos')]
class Index extends Component
{
    public bool   $showModal  = false;
    public ?int   $editing_id = null;

    public ?int  $client_id   = null;
    public string $plate      = '';
    public string $brand      = '';
    public string $model      = '';
    public string $year       = '';
    public string $km_current = '0';
    public bool   $status     = true;
    public string $notes      = '';

    protected function rules(): array
    {
        return [
            'client_id'  => 'required|exists:clients,id',
            'plate'      => 'required|string|max:20',
            'brand'      => 'nullable|string|max:60',
            'model'      => 'nullable|string|max:60',
            'year'       => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
            'km_current' => 'required|integer|min:0',
            'status'     => 'boolean',
            'notes'      => 'nullable|string',
        ];
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    #[On('open-equipment-edit')]
    public function openEdit(int $id): void
    {
        $e = Equipment::findOrFail($id);
        $this->editing_id = $e->id;
        $this->client_id  = $e->client_id;
        $this->plate      = $e->plate;
        $this->brand      = $e->brand ?? '';
        $this->model      = $e->model ?? '';
        $this->year       = $e->year ?? '';
        $this->km_current = $e->km_current;
        $this->status     = $e->status;
        $this->notes      = $e->notes ?? '';
        $this->showModal  = true;
    }

    public function save(): void
    {
        $this->validate();
        $business_id = auth()->user()->business_id;

        $data = [
            'business_id' => $business_id,
            'client_id'   => $this->client_id,
            'plate'       => strtoupper(trim($this->plate)),
            'brand'       => $this->brand ?: null,
            'model'       => $this->model ?: null,
            'year'        => $this->year ?: null,
            'km_current'  => (int) $this->km_current,
            'status'      => $this->status,
            'notes'       => $this->notes ?: null,
            'created_by'  => auth()->id(),
        ];

        if ($this->editing_id) {
            Equipment::findOrFail($this->editing_id)->update($data);
            $this->dispatch('swal', ['title' => 'Equipo actualizado', 'icon' => 'success']);
        } else {
            Equipment::create($data);
            $this->dispatch('swal', ['title' => 'Equipo registrado', 'icon' => 'success']);
        }

        $this->closeModal();
    }

    #[On('equipment-deleted')]
    public function onRecordDeleted(): void {}

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->editing_id = null;
        $this->client_id  = null;
        $this->plate = $this->brand = $this->model = $this->notes = '';
        $this->year      = '';
        $this->km_current = '0';
        $this->status    = true;
        $this->resetValidation();
    }

    public function render()
    {
        $business_id = auth()->user()->business_id;

        $clients = Client::where('business_id', $business_id)->where('status', true)->orderBy('name')->get();

        $stats = [
            'total'  => Equipment::where('business_id', $business_id)->count(),
            'active' => Equipment::where('business_id', $business_id)->where('status', true)->count(),
        ];

        return view('livewire.admin.workshop.equipment.index', compact('clients', 'stats'));
    }
}
