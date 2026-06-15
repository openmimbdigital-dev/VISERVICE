<?php

namespace App\Livewire\Admin\Businesses;

use App\Models\Business;
use App\Models\BusinessType;
use App\Models\City;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
#[Title('Detalle del Negocio')]
class Show extends Component
{
    use WithFileUploads;

    public Business $business;

    // Campos editables
    public string $name = '';
    public string $nit = '';
    public ?int $business_type_id = null;
    public string $phone_number = '';
    public string $email = '';
    public string $address = '';
    public ?int $city_id = null;
    public string $website = '';
    public string $facebook = '';
    public string $instagram = '';
    public string $twitter = '';
    public bool $status = true;

    // Representante legal
    public string $rep_name = '';
    public string $rep_phone = '';
    public string $rep_email = '';

    // Logo
    public string $current_logo = '';
    public $new_logo = null;
    public bool $remove_logo = false;

    public function mount(Business $business): void
    {
        $this->business = $business;
        $this->syncForm();
    }

    private function syncForm(): void
    {
        $b = $this->business;

        $this->name             = $b->name;
        $this->nit              = $b->nit ?? '';
        $this->business_type_id = $b->business_type_id;
        $this->phone_number     = $b->phone_number ?? '';
        $this->email            = $b->email ?? '';
        $this->address          = $b->address ?? '';
        $this->city_id          = $b->city_id;
        $this->website          = $b->website ?? '';
        $this->facebook         = $b->facebook ?? '';
        $this->instagram        = $b->instagram ?? '';
        $this->twitter          = $b->twitter ?? '';
        $this->status           = (bool) $b->status;
        $this->current_logo     = $b->logo ?? '';

        $rep = is_array($b->representative) ? $b->representative : [];
        $this->rep_name  = $rep['name']  ?? '';
        $this->rep_phone = $rep['phone'] ?? '';
        $this->rep_email = $rep['email'] ?? '';
    }

    public function save(): void
    {
        $this->validate([
            'name'             => 'required|string|min:3|max:150',
            'nit'              => 'required|string|max:30|unique:businesses,nit,' . $this->business->id,
            'business_type_id' => 'nullable|exists:business_types,id',
            'phone_number'     => 'nullable|string|max:20',
            'email'            => 'nullable|email|max:150',
            'address'          => 'nullable|string|max:255',
            'city_id'          => 'nullable|exists:cities,id',
            'website'          => 'nullable|url|max:255',
            'rep_name'         => 'nullable|string|max:150',
            'rep_phone'        => 'nullable|string|max:20',
            'rep_email'        => 'nullable|email|max:150',
            'new_logo'         => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ], [
            'name.required'  => 'El nombre del comercio es obligatorio.',
            'nit.required'   => 'El NIT es obligatorio.',
            'nit.unique'     => 'Ya existe otro comercio con este NIT.',
            'email.email'    => 'El correo no es válido.',
            'website.url'    => 'La URL del sitio web no es válida.',
            'rep_email.email'=> 'El correo del representante no es válido.',
            'new_logo.image' => 'El logo debe ser una imagen.',
            'new_logo.mimes' => 'El logo debe ser JPG, PNG o WebP.',
            'new_logo.max'   => 'El logo no debe superar 2 MB.',
        ]);

        $logoPath = $this->business->logo;

        if ($this->remove_logo && $logoPath) {
            Storage::disk('public')->delete($logoPath);
            $logoPath = null;
        }

        if ($this->new_logo) {
            if ($logoPath) Storage::disk('public')->delete($logoPath);
            $logoPath = $this->new_logo->store('business-logos', 'public');
        }

        $this->business->update([
            'name'             => $this->name,
            'nit'              => $this->nit,
            'business_type_id' => $this->business_type_id,
            'phone_number'     => $this->phone_number ?: null,
            'email'            => $this->email ?: null,
            'address'          => $this->address ?: null,
            'city_id'          => $this->city_id,
            'website'          => $this->website ?: null,
            'facebook'         => $this->facebook ?: null,
            'instagram'        => $this->instagram ?: null,
            'twitter'          => $this->twitter ?: null,
            'status'           => $this->status,
            'logo'             => $logoPath,
            'representative'   => array_filter([
                'name'  => $this->rep_name,
                'phone' => $this->rep_phone,
                'email' => $this->rep_email,
            ]) ?: null,
        ]);

        $this->business->refresh();
        $this->current_logo = $this->business->logo ?? '';
        $this->new_logo     = null;
        $this->remove_logo  = false;

        $this->dispatch('swal', ['title' => 'Negocio actualizado correctamente.', 'icon' => 'success']);
    }

    public function toggleStatus(): void
    {
        $this->business->update(['status' => ! $this->business->status]);
        $this->business->refresh();
        $this->status = (bool) $this->business->status;
        $label = $this->status ? 'activado' : 'desactivado';
        $this->dispatch('swal', ['title' => "Comercio {$label}.", 'icon' => 'success']);
    }

    public function toggleUserStatus(int $userId): void
    {
        $user = $this->business->users()->findOrFail($userId);

        // No se puede desactivar al usuario principal (menor ID en el negocio)
        $primaryId = $this->business->users()->orderBy('id')->value('id');
        if ($user->id === $primaryId) {
            $this->dispatch('swal', ['title' => 'No se puede desactivar al usuario principal del comercio.', 'icon' => 'warning']);
            return;
        }

        $user->update(['status' => ! $user->status]);
        $label = $user->fresh()->status ? 'activado' : 'desactivado';
        $this->dispatch('swal', ['title' => "Usuario {$label}.", 'icon' => 'success']);
    }

    public function render()
    {
        $this->business->loadMissing([
            'business_type',
            'city',
            'users.roles',
            'subscriptions.plan',
            'subscriptions.invoices',
        ]);

        $primaryUserId = $this->business->users->sortBy('id')->first()?->id;

        return view('livewire.admin.businesses.show', [
            'cities'         => City::where('is_active', true)->orderBy('name')->get(),
            'business_types' => BusinessType::where('status', true)->orderBy('name')->get(),
            'primaryUserId'  => $primaryUserId,
        ]);
    }
}
