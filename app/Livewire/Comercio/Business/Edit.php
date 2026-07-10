<?php

namespace App\Livewire\Comercio\Business;

use App\Models\City;
use App\Support\BusinessLogoStorage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
#[Title('Mi Negocio')]
class Edit extends Component
{
    use WithFileUploads;

    public string $name = '';
    public string $nit = '';
    public string $phone_number = '';
    public string $email = '';
    public string $address = '';
    public ?int $city_id = null;
    public string $website = '';
    public string $tagline = '';
    public string $tax_regime = '';
    public ?string $current_logo_url = null;
    public $new_logo = null;
    public bool $remove_logo = false;

    public function mount(): void
    {
        $business = auth()->user()->business;

        abort_unless($business, 403, 'No tienes un comercio asociado.');

        $this->name             = $business->name;
        $this->nit              = $business->nit;
        $this->phone_number     = $business->phone_number ?? '';
        $this->email            = $business->email ?? '';
        $this->address          = $business->address ?? '';
        $this->city_id          = $business->city_id;
        $this->website          = $business->website ?? '';
        $this->tagline          = $business->tagline ?? '';
        $this->tax_regime       = $business->tax_regime ?? '';
        $this->current_logo_url = $business->logo_url;
    }

    public function save(): void
    {
        $this->validate([
            'name'         => 'required|string|min:3|max:150',
            'phone_number' => 'required|string|max:20',
            'email'        => 'nullable|email|max:150',
            'address'      => 'nullable|string|max:255',
            'city_id'      => 'nullable|exists:cities,id',
            'website'      => 'nullable|url|max:255',
            'tagline'      => 'nullable|string|max:200',
            'tax_regime'   => 'nullable|string|max:80',
            'new_logo'     => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ], [
            'name.required'        => 'El nombre del comercio es obligatorio.',
            'name.min'             => 'El nombre debe tener al menos 3 caracteres.',
            'phone_number.required'=> 'El teléfono es obligatorio.',
            'email.email'          => 'El correo no es válido.',
            'website.url'          => 'La URL del sitio web no es válida.',
            'new_logo.image'       => 'El logo debe ser una imagen.',
            'new_logo.mimes'       => 'El logo debe ser JPG, PNG o WebP.',
            'new_logo.max'         => 'El logo no debe superar 2MB.',
        ]);

        $business = auth()->user()->business;

        if ($this->remove_logo) {
            BusinessLogoStorage::deleteForBusiness($business->id, $business->logo);
            $business->update(['logo' => null]);
            $this->current_logo_url = null;
        }

        if ($this->new_logo) {
            $path = BusinessLogoStorage::store($business->id, $this->new_logo, $business->logo);
            $business->update(['logo' => $path]);
            $this->current_logo_url = $business->fresh()->logo_url;
        }

        $business->update([
            'name'         => $this->name,
            'phone_number' => $this->phone_number,
            'email'        => $this->email ?: null,
            'address'      => $this->address ?: null,
            'city_id'      => $this->city_id,
            'website'      => $this->website ?: null,
            'tagline'      => $this->tagline ?: null,
            'tax_regime'   => $this->tax_regime ?: null,
        ]);

        $this->new_logo    = null;
        $this->remove_logo = false;

        $this->dispatch('swal', ['title' => 'Información del negocio actualizada.', 'icon' => 'success']);
    }

    public function render()
    {
        $business = auth()->user()->business;

        return view('livewire.comercio.business.edit', [
            'business' => $business,
            'cities'   => City::where('is_active', true)->orderBy('name')->get(),
        ]);
    }
}
