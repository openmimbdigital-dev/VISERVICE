<?php

namespace App\Livewire\Admin\Workshop\Vehicles;

use App\Models\Client;
use App\Models\Vehicle;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Vehículos')]
class Index extends Component
{
    public bool   $showModal  = false;
    public ?int   $editing_id = null;

    public ?int  $client_id   = null;
    public string $plate      = '';
    public string $brand      = '';
    public string $model      = '';
    public string $year       = '';
    public string $color      = '';
    public string $fuel_type  = 'gasolina';
    public string $engine_cc  = '';
    public string $vin        = '';
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
            'color'      => 'nullable|string|max:40',
            'fuel_type'  => 'required|in:gasolina,diesel,gas,electrico,hibrido,otro',
            'engine_cc'  => 'nullable|string|max:20',
            'vin'        => 'nullable|string|max:50',
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

    #[On('open-vehicle-edit')]
    public function openEdit(int $id): void
    {
        $v = Vehicle::findOrFail($id);
        $this->editing_id = $v->id;
        $this->client_id  = $v->client_id;
        $this->plate      = $v->plate;
        $this->brand      = $v->brand ?? '';
        $this->model      = $v->model ?? '';
        $this->year       = $v->year ?? '';
        $this->color      = $v->color ?? '';
        $this->fuel_type  = $v->fuel_type;
        $this->engine_cc  = $v->engine_cc ?? '';
        $this->vin        = $v->vin ?? '';
        $this->km_current = $v->km_current;
        $this->status     = $v->status;
        $this->notes      = $v->notes ?? '';
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
            'color'       => $this->color ?: null,
            'fuel_type'   => $this->fuel_type,
            'engine_cc'   => $this->engine_cc ?: null,
            'vin'         => $this->vin ?: null,
            'km_current'  => (int) $this->km_current,
            'status'      => $this->status,
            'notes'       => $this->notes ?: null,
            'created_by'  => auth()->id(),
        ];

        if ($this->editing_id) {
            Vehicle::findOrFail($this->editing_id)->update($data);
            $this->dispatch('swal', ['title' => 'Vehículo actualizado', 'icon' => 'success']);
        } else {
            Vehicle::create($data);
            $this->dispatch('swal', ['title' => 'Vehículo registrado', 'icon' => 'success']);
        }

        $this->closeModal();
    }

    #[On('vehicle-deleted')]
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
        $this->plate = $this->brand = $this->model = $this->color = $this->engine_cc = $this->vin = $this->notes = '';
        $this->year      = '';
        $this->fuel_type = 'gasolina';
        $this->km_current = '0';
        $this->status    = true;
        $this->resetValidation();
    }

    public function render()
    {
        $business_id = auth()->user()->business_id;

        $clients = Client::where('business_id', $business_id)->where('status', true)->orderBy('name')->get();

        $stats = [
            'total'  => Vehicle::where('business_id', $business_id)->count(),
            'active' => Vehicle::where('business_id', $business_id)->where('status', true)->count(),
        ];

        return view('livewire.admin.workshop.vehicles.index', compact('clients', 'stats'));
    }
}
