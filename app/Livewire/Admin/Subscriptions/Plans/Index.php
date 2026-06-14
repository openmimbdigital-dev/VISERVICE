<?php

namespace App\Livewire\Admin\Subscriptions\Plans;

use App\Models\SubscriptionPlan;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Planes de Suscripción')]
class Index extends Component
{
    use WithPagination;

    public bool $showModal = false;
    public bool $editing = false;

    public ?int $plan_id = null;
    public string $name = '';
    public string $description = '';
    public float $monthly_price = 0;
    public ?int $max_users = null;
    public bool $is_active = true;
    public int $sort_order = 0;
    public array $features = [];
    public string $new_feature = '';

    // Descuentos por ciclo
    public float $discount_monthly = 0;
    public float $discount_quarterly = 5;
    public float $discount_semiannual = 10;
    public float $discount_annual = 20;

    protected function rules(): array
    {
        return [
            'name'               => 'required|string|max:100',
            'description'        => 'nullable|string',
            'monthly_price'      => 'required|numeric|min:0',
            'max_users'          => 'nullable|integer|min:1',
            'is_active'          => 'boolean',
            'sort_order'         => 'integer|min:0',
            'discount_monthly'   => 'numeric|min:0|max:100',
            'discount_quarterly' => 'numeric|min:0|max:100',
            'discount_semiannual'=> 'numeric|min:0|max:100',
            'discount_annual'    => 'numeric|min:0|max:100',
        ];
    }

    public function addFeature(): void
    {
        $feature = trim($this->new_feature);
        if ($feature !== '' && ! in_array($feature, $this->features)) {
            $this->features[] = $feature;
        }
        $this->new_feature = '';
    }

    public function removeFeature(int $index): void
    {
        array_splice($this->features, $index, 1);
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $plan = SubscriptionPlan::findOrFail($id);
        $this->plan_id   = $plan->id;
        $this->name      = $plan->name;
        $this->description = $plan->description ?? '';
        $this->monthly_price = (float) $plan->monthly_price;
        $this->max_users = $plan->max_users;
        $this->is_active = $plan->is_active;
        $this->sort_order = $plan->sort_order;
        $this->features  = $plan->features ?? [];

        $cycles = $plan->billing_cycles ?? [];
        $this->discount_monthly    = $cycles['monthly']['discount']    ?? 0;
        $this->discount_quarterly  = $cycles['quarterly']['discount']  ?? 5;
        $this->discount_semiannual = $cycles['semiannual']['discount'] ?? 10;
        $this->discount_annual     = $cycles['annual']['discount']     ?? 20;

        $this->editing   = true;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $billing_cycles = [
            'monthly'    => ['months' => 1,  'discount' => $this->discount_monthly],
            'quarterly'  => ['months' => 3,  'discount' => $this->discount_quarterly],
            'semiannual' => ['months' => 6,  'discount' => $this->discount_semiannual],
            'annual'     => ['months' => 12, 'discount' => $this->discount_annual],
        ];

        $data = [
            'name'          => $this->name,
            'description'   => $this->description ?: null,
            'monthly_price' => $this->monthly_price,
            'features'      => $this->features ?: null,
            'billing_cycles'=> $billing_cycles,
            'max_users'     => $this->max_users,
            'is_active'     => $this->is_active,
            'sort_order'    => $this->sort_order,
        ];

        if ($this->editing && $this->plan_id) {
            SubscriptionPlan::findOrFail($this->plan_id)->update($data);
            $this->dispatch('swal', ['title' => 'Plan actualizado', 'icon' => 'success']);
        } else {
            SubscriptionPlan::create($data);
            $this->dispatch('swal', ['title' => 'Plan creado', 'icon' => 'success']);
        }

        $this->closeModal();
    }

    public function toggleActive(int $id): void
    {
        $plan = SubscriptionPlan::findOrFail($id);
        $plan->update(['is_active' => ! $plan->is_active]);
    }

    public function delete(int $id): void
    {
        $plan = SubscriptionPlan::findOrFail($id);
        if ($plan->activeSubscriptions()->count() > 0) {
            $this->dispatch('swal', ['title' => 'No se puede eliminar', 'text' => 'El plan tiene suscripciones activas.', 'icon' => 'error']);
            return;
        }
        $plan->delete();
        $this->dispatch('swal', ['title' => 'Plan eliminado', 'icon' => 'success']);
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->editing = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->plan_id = null;
        $this->name = '';
        $this->description = '';
        $this->monthly_price = 0;
        $this->max_users = null;
        $this->is_active = true;
        $this->sort_order = 0;
        $this->features = [];
        $this->new_feature = '';
        $this->discount_monthly = 0;
        $this->discount_quarterly = 5;
        $this->discount_semiannual = 10;
        $this->discount_annual = 20;
        $this->resetValidation();
    }

    public function render()
    {
        $plans = SubscriptionPlan::withCount(['subscriptions', 'activeSubscriptions'])
            ->orderBy('sort_order')
            ->orderBy('monthly_price')
            ->paginate(15);

        return view('livewire.admin.subscriptions.plans.index', compact('plans'));
    }
}
