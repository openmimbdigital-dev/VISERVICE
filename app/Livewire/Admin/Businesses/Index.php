<?php

namespace App\Livewire\Admin\Businesses;

use App\Models\Business;
use App\Models\BusinessType;
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
        $business = Business::findOrFail($id);

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
        $businesses = Business::with(['business_type', 'city', 'users', 'latestSubscription.plan'])
            ->when($this->search, fn ($q) => $q->where(fn ($s) =>
                $s->where('name', 'like', "%{$this->search}%")
                  ->orWhere('nit', 'like', "%{$this->search}%")
                  ->orWhereHas('business_type', fn ($t) =>
                      $t->where('name', 'like', "%{$this->search}%")
                        ->orWhere('label', 'like', '%' . BusinessType::normalizeLabel($this->search) . '%')
                  )
            ))
            ->when($this->filter_type, fn ($q) => $q->where('business_type_id', $this->filter_type))
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
            'total'   => Business::count(),
            'active'  => Business::where('status', true)->count(),
            'pending' => Business::whereHas('subscriptions', fn ($q) => $q->where('status', 'pending'))->count(),
        ];

        return view('livewire.admin.businesses.index', [
            'businesses'     => $businesses,
            'business_types' => BusinessType::where('status', true)->orderBy('name')->get(),
            'stats'          => $stats,
        ]);
    }
}
