<?php

namespace App\Livewire\Admin\Workshop\Clients;

use App\Actions\Workshop\Clients\CreateOrUpdateClientAction;
use App\Livewire\Forms\Admin\Workshop\ClientForm;
use App\Models\Business;
use App\Models\Client;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Cliente')]
class Form extends Component
{
    public ClientForm $form;

    public function mount(?Client $client = null): void
    {
        if (! $client) {
            return;
        }

        if (! auth()->user()->hasRole('superAdmin')) {
            abort_unless($client->business_id === auth()->user()->business_id, 403);
        }

        $this->form->setClient($client);
    }

    public function save(): void
    {
        $data = $this->form->validated();

        CreateOrUpdateClientAction::run(
            $this->form->resolvedBusinessId(),
            $this->form->client_id,
            $data
        );

        $this->dispatch('swal', [
            'title' => $this->form->isEditing() ? 'Cliente actualizado' : 'Cliente creado',
            'icon'  => 'success',
        ]);

        $this->redirectRoute('admin.workshop.clients.index', navigate: true);
    }

    public function render()
    {
        $is_super_admin = auth()->user()->hasRole('superAdmin');

        return view('livewire.admin.workshop.clients.form', [
            'is_editing'     => $this->form->isEditing(),
            'is_super_admin' => $is_super_admin,
            'businesses'     => $is_super_admin
                ? Business::where('status', true)->orderBy('name')->get(['id', 'name'])
                : collect(),
        ]);
    }
}
