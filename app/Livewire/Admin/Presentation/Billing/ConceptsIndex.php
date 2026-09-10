<?php

namespace App\Livewire\Admin\Presentation\Billing;

use App\Support\Presentation\BillingDemoData;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Presentación — Conceptos de cobro')]
class ConceptsIndex extends Component
{
    public bool $showModal = false;

    public string $name = '';

    public string $amount = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->hasRole('superAdmin'), 403);
    }

    public function openCreate(): void
    {
        $this->reset(['name', 'amount']);
        $this->showModal = true;
        $this->resetValidation();
    }

    public function closeModal(): void
    {
        $this->showModal = false;
    }

    public function save(): void
    {
        $this->validate([
            'name' => 'required|string|max:80',
            'amount' => 'required|integer|min:1',
        ], [
            'name.required' => 'El nombre es obligatorio.',
            'amount.required' => 'El valor es obligatorio.',
        ]);

        BillingDemoData::addConcept([
            'id' => 'con-custom-'.uniqid(),
            'name' => trim($this->name),
            'amount' => (int) $this->amount,
            'active' => true,
        ]);

        $this->closeModal();
        $this->dispatch('swal', ['title' => 'Concepto creado', 'icon' => 'success']);
    }

    public function render()
    {
        return view('livewire.admin.presentation.billing.concepts-index', [
            'concepts' => BillingDemoData::concepts(),
        ]);
    }
}
