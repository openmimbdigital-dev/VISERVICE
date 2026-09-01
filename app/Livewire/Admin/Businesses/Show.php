<?php

namespace App\Livewire\Admin\Businesses;

use App\Models\Business;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Detalle del Negocio')]
class Show extends Component
{
    public Business $business;

    public function mount(Business $business): void
    {
        abort_unless(auth()->user()?->can('businesses.view'), 403);

        $this->business = $business;
    }

    public function toggleStatus(): void
    {
        if ($this->business->status) {
            abort_unless(auth()->user()?->can('businesses.deactivate'), 403);
        } else {
            abort_unless(auth()->user()?->can('businesses.activate'), 403);
        }

        $this->business->update(['status' => ! $this->business->status]);
        $this->business->refresh();

        $label = $this->business->status ? 'activado' : 'desactivado';
        $this->dispatch('swal', ['title' => "Negocio {$label}.", 'icon' => 'success']);
    }

    public function toggleUserStatus(int $user_id): void
    {
        $user = $this->business->users()->findOrFail($user_id);

        if ($user->status) {
            abort_unless(auth()->user()?->can('users.deactivate'), 403);
        } else {
            abort_unless(auth()->user()?->can('users.activate'), 403);
        }

        $primary_id = $this->business->users()
            ->wherePivot('is_primary', true)
            ->orderBy('users.id')
            ->value('users.id')
            ?? $this->business->users()->orderBy('users.id')->value('users.id');

        if ($user->id === $primary_id) {
            $this->dispatch('swal', ['title' => 'No se puede desactivar al usuario principal del negocio.', 'icon' => 'warning']);

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
            'organization_type',
            'city.country',
            'users.roles',
            'subscriptions.plan',
            'subscriptions.invoices',
        ]);

        $primary_user_id = $this->business->users
            ->sortBy(fn (User $user) => [(int) ! (bool) $user->pivot->is_primary, $user->id])
            ->first()?->id;

        return view('livewire.admin.businesses.show', [
            'primary_user_id' => $primary_user_id,
        ]);
    }
}
