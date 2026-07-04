<?php

namespace App\Livewire\Admin\Businesses;

use App\Actions\Business\CreateOrUpdateBusinessAction;
use App\Livewire\Forms\Admin\Businesses\BusinessForm;
use App\Models\Business;
use App\Models\City;
use App\Models\OrganizationType;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
#[Title('Negocio')]
class Form extends Component
{
    use WithFileUploads;

    public BusinessForm $form;

    public ?string $current_logo_url = null;

    public $new_logo = null;

    public bool $remove_logo = false;

    /** Estado inicial al cargar edición (debe ser public para persistir entre requests Livewire). */
    public bool $original_status = false;

    public function mount(?Business $business = null): void
    {
        if ($business) {
            abort_unless(auth()->user()?->can('businesses.edit'), 403);

            abort_unless(
                Business::query()->forAuthUser()->whereKey($business->id)->exists(),
                404
            );

            $this->form->setBusiness($business);
            $this->current_logo_url = $business->logo_url;
            $this->original_status  = (bool) $business->status;

            return;
        }

        abort_unless(auth()->user()?->can('businesses.create'), 403);
    }

    private function authorizeStatusChange(bool $new_status): void
    {
        if ($new_status) {
            abort_unless(auth()->user()?->can('businesses.activate'), 403);

            return;
        }

        abort_unless(auth()->user()?->can('businesses.deactivate'), 403);
    }

    public function save(): void
    {
        abort_unless(
            $this->form->isEditing()
                ? auth()->user()?->can('businesses.edit')
                : auth()->user()?->can('businesses.create'),
            403
        );

        $update_status = false;

        if ($this->form->isEditing() && $this->form->status !== $this->original_status) {
            $this->authorizeStatusChange($this->form->status);
            $update_status = true;
        }

        if (! $this->form->isEditing() && ! $this->form->status) {
            abort_unless(auth()->user()?->can('businesses.deactivate'), 403);
        }

        if ($this->form->isEditing() && ! $this->canEditStatus()) {
            $this->form->status = $this->original_status;
        }

        if ($this->new_logo) {
            $this->validate([
                'new_logo' => 'image|mimes:jpg,jpeg,jfif,png,webp|max:2048',
            ], [
                'new_logo.image' => 'El logo debe ser una imagen.',
                'new_logo.mimes' => 'El logo debe ser JPG, PNG o WebP.',
                'new_logo.max'   => 'El logo no debe superar 2 MB.',
            ]);
        }

        CreateOrUpdateBusinessAction::run(
            $this->form->business_id,
            $this->form->validated(),
            $this->new_logo,
            $this->remove_logo,
            $update_status
        );

        $this->dispatch('swal', [
            'title' => $this->form->isEditing() ? 'Negocio actualizado.' : 'Negocio creado.',
            'icon'  => 'success',
        ]);

        $this->redirectRoute('admin.businesses.index', navigate: true);
    }

    private function canEditStatus(): bool
    {
        return auth()->user()?->can('businesses.activate')
            || auth()->user()?->can('businesses.deactivate');
    }

    public function render()
    {
        return view('livewire.admin.businesses.form', [
            'is_editing'          => $this->form->isEditing(),
            'organization_types'  => OrganizationType::query()
                ->where('active', true)
                ->orderBy('name')
                ->get(),
            'cities'              => City::query()->where('is_active', true)->orderBy('name')->get(),
            'can_edit_status'     => $this->canEditStatus(),
        ]);
    }
}
