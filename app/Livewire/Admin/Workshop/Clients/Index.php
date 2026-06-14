<?php

namespace App\Livewire\Admin\Workshop\Clients;

use App\Models\Client;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Clientes')]
class Index extends Component
{
    public bool   $showModal  = false;
    public ?int   $editing_id = null;

    public string $name            = '';
    public string $document_type   = 'CC';
    public string $document_number = '';
    public string $phone           = '';
    public string $email           = '';
    public string $address         = '';
    public string $contact_name    = '';
    public bool   $status          = true;
    public string $notes           = '';

    protected function rules(): array
    {
        return [
            'name'            => 'required|string|max:150',
            'document_type'   => 'required|in:CC,NIT,CE,PA,PPT,TI',
            'document_number' => 'nullable|string|max:30',
            'phone'           => 'nullable|string|max:30',
            'email'           => 'nullable|email|max:100',
            'address'         => 'nullable|string|max:200',
            'contact_name'    => 'nullable|string|max:100',
            'status'          => 'boolean',
            'notes'           => 'nullable|string',
        ];
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    #[On('open-client-edit')]
    public function openEdit(int $id): void
    {
        $client = Client::findOrFail($id);
        $this->editing_id      = $client->id;
        $this->name            = $client->name;
        $this->document_type   = $client->document_type;
        $this->document_number = $client->document_number ?? '';
        $this->phone           = $client->phone ?? '';
        $this->email           = $client->email ?? '';
        $this->address         = $client->address ?? '';
        $this->contact_name    = $client->contact_name ?? '';
        $this->status          = $client->status;
        $this->notes           = $client->notes ?? '';
        $this->showModal       = true;
    }

    public function save(): void
    {
        $this->validate();
        $business_id = auth()->user()->business_id;

        $data = [
            'business_id'     => $business_id,
            'name'            => $this->name,
            'document_type'   => $this->document_type,
            'document_number' => $this->document_number ?: null,
            'phone'           => $this->phone ?: null,
            'email'           => $this->email ?: null,
            'address'         => $this->address ?: null,
            'contact_name'    => $this->contact_name ?: null,
            'status'          => $this->status,
            'notes'           => $this->notes ?: null,
            'created_by'      => auth()->id(),
        ];

        if ($this->editing_id) {
            Client::findOrFail($this->editing_id)->update($data);
            $this->dispatch('swal', ['title' => 'Cliente actualizado', 'icon' => 'success']);
        } else {
            Client::create($data);
            $this->dispatch('swal', ['title' => 'Cliente creado', 'icon' => 'success']);
        }

        $this->closeModal();
    }

    #[On('client-deleted')]
    public function onRecordDeleted(): void {}

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->editing_id      = null;
        $this->name            = '';
        $this->document_type   = 'CC';
        $this->document_number = '';
        $this->phone           = '';
        $this->email           = '';
        $this->address         = '';
        $this->contact_name    = '';
        $this->status          = true;
        $this->notes           = '';
        $this->resetValidation();
    }

    public function render()
    {
        $business_id = auth()->user()->business_id;

        $stats = [
            'total'  => Client::where('business_id', $business_id)->count(),
            'active' => Client::where('business_id', $business_id)->where('status', true)->count(),
        ];

        return view('livewire.admin.workshop.clients.index', compact('stats'));
    }
}
