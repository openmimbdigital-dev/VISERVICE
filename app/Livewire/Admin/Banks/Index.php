<?php

namespace App\Livewire\Admin\Banks;

use App\Models\Bank;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Bancos')]
class Index extends Component
{
    use WithFileUploads, WithPagination;

    public string $search = '';
    public bool $showModal = false;
    public ?int $selected_id = null;

    public string $name = '';
    public string $code = '';
    public bool $is_active = true;

    public string $current_logo = '';
    public $new_logo = null;
    public bool $remove_logo = false;

    protected function rules(): array
    {
        $unique = $this->selected_id
            ? 'unique:banks,name,' . $this->selected_id
            : 'unique:banks,name';

        return [
            'name'      => "required|string|max:100|{$unique}",
            'code'      => 'nullable|string|max:10',
            'is_active' => 'boolean',
            'new_logo'  => 'nullable|image|mimes:jpg,jpeg,png,webp,svg|max:2048',
        ];
    }

    protected $messages = [
        'name.required' => 'El nombre del banco es obligatorio.',
        'name.unique'   => 'Ya existe un banco con ese nombre.',
        'name.max'      => 'El nombre no debe superar 100 caracteres.',
        'code.max'      => 'El código no debe superar 10 caracteres.',
        'new_logo.image'  => 'El logo debe ser una imagen.',
        'new_logo.mimes'  => 'El logo debe ser JPG, PNG, WebP o SVG.',
        'new_logo.max'    => 'El logo no debe superar 2 MB.',
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $bank = Bank::findOrFail($id);

        $this->selected_id  = $bank->id;
        $this->name         = $bank->name;
        $this->code         = $bank->code ?? '';
        $this->is_active    = $bank->is_active;
        $this->current_logo = $bank->logo ?? '';
        $this->new_logo     = null;
        $this->remove_logo  = false;

        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $logoPath = $this->current_logo ?: null;

        if ($this->remove_logo && $logoPath) {
            Storage::disk('public')->delete($logoPath);
            $logoPath = null;
        }

        if ($this->new_logo) {
            if ($logoPath) {
                Storage::disk('public')->delete($logoPath);
            }
            $logoPath = $this->new_logo->store('bank-logos', 'public');
        }

        $data = [
            'name'      => $this->name,
            'code'      => $this->code ?: null,
            'logo'      => $logoPath,
            'is_active' => $this->is_active,
        ];

        if ($this->selected_id) {
            Bank::findOrFail($this->selected_id)->update($data);
            $this->dispatch('swal', ['title' => 'Banco actualizado.', 'icon' => 'success']);
        } else {
            Bank::create($data);
            $this->dispatch('swal', ['title' => 'Banco creado.', 'icon' => 'success']);
        }

        $this->closeModal();
    }

    public function toggleActive(int $id): void
    {
        $bank = Bank::findOrFail($id);
        $bank->update(['is_active' => ! $bank->is_active]);

        $label = $bank->fresh()->is_active ? 'activado' : 'desactivado';
        $this->dispatch('swal', ['title' => "Banco {$label}.", 'icon' => 'success']);
    }

    public function delete(int $id): void
    {
        $bank = Bank::withCount('bankAccounts')->findOrFail($id);

        if ($bank->bank_accounts_count > 0) {
            $this->dispatch('swal', [
                'title' => 'No se puede eliminar',
                'text'  => 'Este banco tiene cuentas bancarias asociadas.',
                'icon'  => 'error',
            ]);
            return;
        }

        if ($bank->logo) {
            Storage::disk('public')->delete($bank->logo);
        }

        $bank->delete();
        $this->dispatch('swal', ['title' => 'Banco eliminado.', 'icon' => 'warning']);
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->selected_id  = null;
        $this->name         = '';
        $this->code         = '';
        $this->is_active    = true;
        $this->current_logo = '';
        $this->new_logo     = null;
        $this->remove_logo  = false;
        $this->resetValidation();
    }

    public function render()
    {
        $banks = Bank::withCount('bankAccounts')
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%")
                ->orWhere('code', 'like', "%{$this->search}%"))
            ->orderBy('name')
            ->paginate(15);

        return view('livewire.admin.banks.index', compact('banks'));
    }
}
