<?php

namespace App\Livewire\Admin\Workshop\Clients;

use App\Actions\Workshop\Clients\CreateOrUpdateClientAction;
use App\Livewire\Forms\Admin\Workshop\ClientForm;
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

        abort_unless($client->business_id === auth()->user()->business_id, 403);

        $this->form->setClient($client);
    }

    public function save(): void
    {
        $data = $this->form->validated();

        CreateOrUpdateClientAction::run(
            auth()->user()->business_id,
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
        return view('livewire.admin.workshop.clients.form', [
            'is_editing' => $this->form->isEditing(),
        ]);
    }
}
