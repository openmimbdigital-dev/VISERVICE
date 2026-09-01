<?php

namespace App\Livewire\Admin\BankAccounts;

use App\Models\Bank;
use App\Models\BankAccount;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
#[Title('Cuentas Bancarias')]
class Index extends Component
{
    use WithFileUploads;

    public bool $showModal = false;
    public ?int $selected_id = null;

    // Campos del formulario
    public ?int $bank_id = null;
    public string $account_type = 'ahorros';
    public string $account_number = '';
    public string $account_holder = '';
    public string $document_type = 'NIT';
    public string $document_number = '';
    public bool $is_active = true;
    public string $notes = '';

    // Logo
    public string $current_logo = '';
    public $new_logo = null;
    public bool $remove_logo = false;

    protected function rules(): array
    {
        $uniqueRule = $this->selected_id
            ? 'unique:bank_accounts,account_number,' . $this->selected_id
            : 'unique:bank_accounts,account_number';

        return [
            'bank_id'         => 'required|exists:banks,id',
            'account_type'    => 'required|in:corriente,ahorros',
            'account_number'  => "required|string|max:50|{$uniqueRule}",
            'account_holder'  => 'required|string|max:150',
            'document_type'   => 'required|string|max:20',
            'document_number' => 'required|string|max:30',
            'is_active'       => 'boolean',
            'notes'           => 'nullable|string|max:500',
            'new_logo'        => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ];
    }

    protected $messages = [
        'bank_id.required'         => 'Selecciona un banco.',
        'bank_id.exists'           => 'El banco seleccionado no es válido.',
        'account_type.required'    => 'Selecciona el tipo de cuenta.',
        'account_type.in'          => 'Tipo de cuenta no válido.',
        'account_number.required'  => 'El número de cuenta es obligatorio.',
        'account_number.unique'    => 'Ya existe una cuenta con este número.',
        'account_holder.required'  => 'El titular de la cuenta es obligatorio.',
        'document_type.required'   => 'El tipo de documento es obligatorio.',
        'document_number.required' => 'El número de documento es obligatorio.',
        'new_logo.image'           => 'El logo debe ser una imagen.',
        'new_logo.mimes'           => 'El logo debe ser JPG, PNG o WebP.',
        'new_logo.max'             => 'El logo no debe superar 2 MB.',
    ];

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $account = BankAccount::findOrFail($id);

        $this->selected_id     = $account->id;
        $this->bank_id         = $account->bank_id;
        $this->account_type    = $account->account_type;
        $this->account_number  = $account->account_number;
        $this->account_holder  = $account->account_holder;
        $this->document_type   = $account->document_type;
        $this->document_number = $account->document_number;
        $this->is_active       = $account->is_active;
        $this->notes           = $account->notes ?? '';
        $this->current_logo    = $account->logo ?? '';
        $this->new_logo        = null;
        $this->remove_logo     = false;

        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        // Gestión del logo
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
            'bank_id'         => $this->bank_id,
            'logo'            => $logoPath,
            'account_type'    => $this->account_type,
            'account_number'  => $this->account_number,
            'account_holder'  => $this->account_holder,
            'document_type'   => $this->document_type,
            'document_number' => $this->document_number,
            'is_active'       => $this->is_active,
            'notes'           => $this->notes ?: null,
        ];

        if ($this->selected_id) {
            BankAccount::findOrFail($this->selected_id)->update($data);
            $this->dispatch('swal', ['title' => 'Cuenta actualizada correctamente.', 'icon' => 'success']);
        } else {
            BankAccount::create($data);
            $this->dispatch('swal', ['title' => 'Cuenta bancaria creada.', 'icon' => 'success']);
        }

        $this->closeModal();
    }

    public function toggleActive(int $id): void
    {
        $account = BankAccount::findOrFail($id);
        $account->update(['is_active' => ! $account->is_active]);

        $label = $account->fresh()->is_active ? 'activada' : 'desactivada';
        $this->dispatch('swal', ['title' => "Cuenta {$label}.", 'icon' => 'success']);
    }

    public function delete(int $id): void
    {
        $account = BankAccount::findOrFail($id);

        if ($account->logo) {
            Storage::disk('public')->delete($account->logo);
        }

        $account->delete();
        $this->dispatch('swal', ['title' => 'Cuenta eliminada.', 'icon' => 'warning']);
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->selected_id     = null;
        $this->bank_id         = null;
        $this->account_type    = 'ahorros';
        $this->account_number  = '';
        $this->account_holder  = '';
        $this->document_type   = 'NIT';
        $this->document_number = '';
        $this->is_active       = true;
        $this->notes           = '';
        $this->current_logo    = '';
        $this->new_logo        = null;
        $this->remove_logo     = false;
        $this->resetValidation();
    }

    public function render()
    {
        $accounts = BankAccount::with('bank')->orderByDesc('is_active')->orderBy('id')->get();
        $banks    = Bank::where('is_active', true)->orderBy('name')->get();

        return view('livewire.admin.bank-accounts.index', compact('accounts', 'banks'));
    }
}
