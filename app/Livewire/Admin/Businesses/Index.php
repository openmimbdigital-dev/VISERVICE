<?php

namespace App\Livewire\Admin\Businesses;

use App\Models\Business;
use App\Models\OrganizationType;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Negocios Registrados')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filter_type = '';
    public string $filter_subscription = '';
    public string $filter_status = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('businesses.view'), 403);
    }

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingFilterType(): void { $this->resetPage(); }
    public function updatingFilterSubscription(): void { $this->resetPage(); }
    public function updatingFilterStatus(): void { $this->resetPage(); }

    public function toggleStatus(int $id): void
    {
        $business = Business::query()->forAuthUser()->findOrFail($id);

        if ($business->status) {
            abort_unless(auth()->user()?->can('businesses.deactivate'), 403);
        } else {
            abort_unless(auth()->user()?->can('businesses.activate'), 403);
        }

        $business->update(['status' => ! $business->status]);

        $label = $business->fresh()->status ? 'activado' : 'desactivado';
        $this->dispatch('swal', ['title' => "Comercio {$label}.", 'icon' => 'success']);
    }

    public function render()
    {
        $user = auth()->user();
        $base_query = Business::query()->forAuthUser();

        $businesses = (clone $base_query)
            ->with(['organization_type', 'city', 'users', 'latestSubscription.plan'])
            ->when($this->search, fn ($q) => $q->where(fn ($s) =>
                $s->where('name', 'like', "%{$this->search}%")
                  ->orWhere('nit', 'like', "%{$this->search}%")
                  ->orWhereHas('organization_type', fn ($t) =>
                      $t->where('name', 'like', "%{$this->search}%")
                        ->orWhere('label', 'like', '%' . OrganizationType::normalizeLabel($this->search) . '%')
                  )
            ))
            ->when($this->filter_type, fn ($q) => $q->where('organization_type_id', $this->filter_type))
            ->when($this->filter_status !== '', fn ($q) => $q->where('status', (bool) $this->filter_status))
            ->when($this->filter_subscription, function ($q) {
                return match ($this->filter_subscription) {
                    'pending' => $q->whereHas('subscriptions', fn ($s) => $s->where('status', 'pending')),
                    'active'  => $q->whereHas('subscriptions', fn ($s) => $s->where('status', 'active')),
                    'trial'   => $q->whereHas('subscriptions', fn ($s) => $s->where('status', 'trial')),
                    'none'    => $q->whereDoesntHave('subscriptions'),
                    default   => $q,
                };
            })
            ->latest()
            ->paginate(15);

        $stats = [
            'total'   => (clone $base_query)->count(),
            'active'  => (clone $base_query)->where('status', true)->count(),
            'pending' => (clone $base_query)->whereHas('subscriptions', fn ($q) => $q->where('status', 'pending'))->count(),
        ];

        $organization_types_query = OrganizationType::query()->where('status', true);

        if ($user && ! $user->hasRole('superAdmin')) {
            $business_ids = $user->businessIds();

            $organization_types_query->when(
                $business_ids !== [],
                fn ($q) => $q->whereHas('businesses', fn ($b) => $b->whereIn('businesses.id', $business_ids)),
                fn ($q) => $q->whereRaw('0 = 1')
            );
        }

        return view('livewire.admin.businesses.index', [
            'businesses'          => $businesses,
            'organization_types'  => $organization_types_query->orderBy('name')->get(),
            'stats'               => $stats,
            'shows_all'           => $user?->hasRole('superAdmin') ?? false,
        ]);
    }
}
