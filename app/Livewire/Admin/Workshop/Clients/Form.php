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

    private ?bool $original_status = null;

    public function mount(?Client $client = null): void
    {
        if ($client) {
            abort_unless(auth()->user()->can('workshop.clients.edit'), 403);

            if (! auth()->user()->hasRole('superAdmin')) {
                abort_unless($client->business_id === auth()->user()->business_id, 403);
            }

            $this->form->setClient($client);
            $this->original_status = $client->status;

            return;
        }

        abort_unless(auth()->user()->can('workshop.clients.create'), 403);
    }

    private function authorizeStatusChange(bool $new_status): void
    {
        if ($new_status) {
            abort_unless(auth()->user()->can('workshop.clients.activate'), 403);

            return;
        }

        abort_unless(auth()->user()->can('workshop.clients.deactivate'), 403);
    }

    public function save(): void
    {
        abort_unless(
            $this->form->isEditing()
                ? auth()->user()->can('workshop.clients.edit')
                : auth()->user()->can('workshop.clients.create'),
            403
        );

        if ($this->form->isEditing() && $this->form->status !== $this->original_status) {
            $this->authorizeStatusChange($this->form->status);
        }

        if (! $this->form->isEditing() && ! $this->form->status) {
            abort_unless(auth()->user()->can('workshop.clients.deactivate'), 403);
        }

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
            'is_editing'            => $this->form->isEditing(),
            'is_super_admin'        => $is_super_admin,
            'can_edit_status_in_form' => (! $this->form->isEditing()
                    && (auth()->user()->can('workshop.clients.activate') || auth()->user()->can('workshop.clients.deactivate')))
                || ($this->form->isEditing()
                    && (auth()->user()->can('workshop.clients.activate') || auth()->user()->can('workshop.clients.deactivate'))),
            'businesses'            => $is_super_admin
                ? Business::where('status', true)->orderBy('name')->get(['id', 'name'])
                : collect(),
        ]);
    }
}
