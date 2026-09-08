<?php

namespace App\Livewire\Ui\SearchableCreate;

use App\Actions\Workshop\Clients\CreateOrUpdateClientAction;
use App\Livewire\Forms\Admin\Workshop\ClientForm;
use App\Models\Business;
use App\Models\City;
use Livewire\Component;

class ClientModal extends Component
{
    public ClientForm $form;

    public function mount(string $search = ''): void
    {
        abort_unless(auth()->user()?->can('workshop.clients.create'), 403);

        $this->form->document_number = $search;
        $this->form->status = true;

        if (! auth()->user()->hasRole('superAdmin')) {
            $this->form->business_id = auth()->user()->business_id;
        }
    }

    public function updatedFormDocumentType(): void
    {
        $this->form->person_type = $this->form->document_type === 'NIT' ? 1 : 2;
        $this->fillVerificationDigit();
    }

    public function updatedFormDocumentNumber(): void
    {
        $this->fillVerificationDigit();
    }

    public function close(): void
    {
        $this->dispatch('searchable-create-closed');
    }

    public function save(): void
    {
        abort_unless(auth()->user()->can('workshop.clients.create'), 403);

        $client = CreateOrUpdateClientAction::run(
            $this->form->resolvedBusinessId(),
            null,
            $this->form->validated()
        );

        $this->dispatch('swal', [
            'title' => 'Cliente creado',
            'icon'  => 'success',
        ]);

        $this->dispatch('searchable-created', id: $client->id);
    }

    public function render()
    {
        $is_super_admin = auth()->user()->hasRole('superAdmin');

        return view('livewire.ui.searchable-create.client-modal', [
            'is_super_admin' => $is_super_admin,
            'businesses'     => $is_super_admin
                ? Business::query()->where('status', true)->orderBy('name')->get(['id', 'name'])
                : collect(),
            'cities'         => City::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'state_province']),
        ]);
    }

    private function fillVerificationDigit(): void
    {
        if ($this->form->document_type !== 'NIT') {
            $this->form->verification_digit = '';

            return;
        }

        $this->form->verification_digit = (string) ($this->form->suggestedVerificationDigit() ?? '');
    }
}
