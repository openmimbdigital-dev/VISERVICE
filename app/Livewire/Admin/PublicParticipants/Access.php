<?php

namespace App\Livewire\Admin\PublicParticipants;

use App\Models\OrganizationType;
use App\Support\Public\PublicRouteAccess;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Ítems públicos — Participantes')]
class Access extends Component
{
    public ?int $organization_type_id = null;

    /** @var list<string> */
    public array $selected_item_keys = [];

    public function mount(): void
    {
        abort_unless(
            auth()->user()?->hasRole('superAdmin') && auth()->user()?->can('public_routes.manage'),
            403
        );

        $first = OrganizationType::query()->where('status', true)->orderBy('name')->first();
        $this->organization_type_id = $first?->id;
        $this->loadItemsForOrganizationType();
    }

    public function updatedOrganizationTypeId(): void
    {
        $this->loadItemsForOrganizationType();
    }

    public function save(): void
    {
        abort_unless(
            auth()->user()?->hasRole('superAdmin') && auth()->user()?->can('public_routes.manage'),
            403
        );

        $this->validate([
            'organization_type_id' => ['required', 'integer', 'exists:organization_types,id'],
            'selected_item_keys' => ['array'],
            'selected_item_keys.*' => ['string', 'in:'.implode(',', array_keys(PublicRouteAccess::items()))],
        ], [
            'organization_type_id.required' => 'Selecciona un tipo de organización.',
            'organization_type_id.exists' => 'El tipo de organización no es válido.',
        ]);

        PublicRouteAccess::syncOrganizationTypeItems(
            (int) $this->organization_type_id,
            $this->selected_item_keys
        );

        $this->dispatch('swal', [
            'title' => 'Ítems visibles actualizados.',
            'icon' => 'success',
        ]);
    }

    public function render()
    {
        return view('livewire.admin.public-participants.access', [
            'organization_types' => OrganizationType::query()
                ->where('status', true)
                ->orderBy('name')
                ->get(['id', 'name']),
            'section_label' => PublicRouteAccess::section()['label'] ?? 'Participantes',
            'items' => PublicRouteAccess::items(),
        ]);
    }

    private function loadItemsForOrganizationType(): void
    {
        if (! $this->organization_type_id) {
            $this->selected_item_keys = [];

            return;
        }

        $this->selected_item_keys = PublicRouteAccess::enabledItemKeysForOrganizationType(
            (int) $this->organization_type_id
        );
    }
}
