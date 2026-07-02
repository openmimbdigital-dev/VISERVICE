<?php

namespace App\Livewire\Admin\Workshop\WorkOrders;

use App\Actions\CreateWorkOrderAction;
use App\Models\Client;
use App\Models\Equipment;
use App\Models\WorkOrder;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Órdenes de Trabajo')]
class Index extends Component
{
    public bool   $showModal = false;

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('workshop.work-orders.view'), 403);
    }

    public ?int   $client_id         = null;
    public ?int   $equipment_id      = null;
    public string $km_entry           = '0';
    public string $diagnosis          = '';
    public string $estimated_delivery = '';
    public string $tax_percentage     = '0';
    public string $notes              = '';

    protected function rules(): array
    {
        return [
            'client_id'          => 'required|exists:clients,id',
            'equipment_id'       => 'required|exists:equipment,id',
            'km_entry'           => 'required|integer|min:0',
            'diagnosis'          => 'nullable|string',
            'estimated_delivery' => 'nullable|date',
            'tax_percentage'     => 'required|numeric|min:0|max:100',
            'notes'              => 'nullable|string',
        ];
    }

    public function updatedClientId(): void { $this->equipment_id = null; }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $workOrder = CreateWorkOrderAction::run(
            auth()->user()->business_id,
            $this->client_id,
            $this->equipment_id,
            [
                'km_entry'           => (int) $this->km_entry,
                'diagnosis'          => $this->diagnosis ?: null,
                'estimated_delivery' => $this->estimated_delivery ?: null,
                'tax_percentage'     => $this->tax_percentage,
                'notes'              => $this->notes ?: null,
                'created_by'         => auth()->id(),
            ]
        );

        $this->closeModal();
        $this->dispatch('swal', ['title' => "OT {$workOrder->reference} creada", 'icon' => 'success']);
        $this->redirectRoute('admin.workshop.work-orders.show', $workOrder->id, navigate: true);
    }

    #[On('work-order-deleted')]
    public function onRecordDeleted(): void {}

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->client_id = $this->equipment_id = null;
        $this->km_entry = '0';
        $this->diagnosis = $this->estimated_delivery = $this->notes = '';
        $this->tax_percentage = '0';
        $this->resetValidation();
    }

    public function render()
    {
        $business_id = auth()->user()->business_id;

        $clients = Client::where('business_id', $business_id)->where('status', true)->orderBy('name')->get();

        $equipment_for_client = $this->client_id
            ? Equipment::where('client_id', $this->client_id)->where('status', true)->orderBy('plate')->get()
            : collect();

        $stats = [
            'abiertas'    => WorkOrder::where('business_id', $business_id)->where('status', 'abierta')->count(),
            'en_proceso'  => WorkOrder::where('business_id', $business_id)->where('status', 'en_proceso')->count(),
            'finalizadas' => WorkOrder::where('business_id', $business_id)->where('status', 'finalizada')->count(),
            'canceladas'  => WorkOrder::where('business_id', $business_id)->where('status', 'cancelada')->count(),
        ];

        return view('livewire.admin.workshop.work-orders.index',
            compact('clients', 'equipment_for_client', 'stats'));
    }
}
