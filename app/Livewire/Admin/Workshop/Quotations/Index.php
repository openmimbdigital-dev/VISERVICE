<?php

namespace App\Livewire\Admin\Workshop\Quotations;

use App\Actions\CreateQuotationAction;
use App\Models\Client;
use App\Models\Quotation;
use App\Models\Equipment;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Cotizaciones')]
class Index extends Component
{
    public bool   $showModal     = false;

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('workshop.quotations.view'), 403);
    }

    public ?int  $client_id      = null;
    public ?int  $equipment_id   = null;
    public string $km_entry       = '0';
    public string $diagnosis      = '';
    public string $valid_until    = '';
    public string $tax_percentage = '0';
    public string $notes          = '';

    protected function rules(): array
    {
        return [
            'client_id'      => 'required|exists:clients,id',
            'equipment_id'   => 'required|exists:equipment,id',
            'km_entry'       => 'required|integer|min:0',
            'diagnosis'      => 'nullable|string',
            'valid_until'    => 'nullable|date',
            'tax_percentage' => 'required|numeric|min:0|max:100',
            'notes'          => 'nullable|string',
        ];
    }

    public function updatedClientId(): void
    {
        $this->equipment_id = null;
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->valid_until = now()->addDays(15)->format('Y-m-d');
        $this->showModal   = true;
    }

    public function save(): void
    {
        $this->validate();

        $quotation = CreateQuotationAction::run(
            auth()->user()->business_id,
            $this->client_id,
            $this->equipment_id,
            [
                'km_entry'       => (int) $this->km_entry,
                'diagnosis'      => $this->diagnosis ?: null,
                'valid_until'    => $this->valid_until ?: null,
                'tax_percentage' => $this->tax_percentage,
                'notes'          => $this->notes ?: null,
                'created_by'     => auth()->id(),
            ]
        );

        $this->closeModal();
        $this->dispatch('swal', ['title' => "Cotización {$quotation->reference} creada", 'icon' => 'success']);
        $this->redirectRoute('admin.workshop.quotations.show', $quotation->id, navigate: true);
    }

    #[On('quotation-deleted')]
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
        $this->diagnosis = $this->valid_until = $this->notes = '';
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
            'total'     => Quotation::where('business_id', $business_id)->count(),
            'borrador'  => Quotation::where('business_id', $business_id)->where('status', 'borrador')->count(),
            'enviada'   => Quotation::where('business_id', $business_id)->where('status', 'enviada')->count(),
            'aceptada'  => Quotation::where('business_id', $business_id)->where('status', 'aceptada')->count(),
            'rechazada' => Quotation::where('business_id', $business_id)->where('status', 'rechazada')->count(),
        ];

        return view('livewire.admin.workshop.quotations.index',
            compact('clients', 'equipment_for_client', 'stats'));
    }
}
