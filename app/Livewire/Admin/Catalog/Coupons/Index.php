<?php

namespace App\Livewire\Admin\Catalog\Coupons;

use App\Actions\Catalog\CreateOrUpdateCouponAction;
use App\Livewire\Forms\Admin\Catalog\CouponForm;
use App\Models\Coupon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Cupones de descuento')]
class Index extends Component
{
    public CouponForm $form;

    public bool $showModal = false;

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('catalog.coupons.view'), 403);
    }

    public function openCreate(): void
    {
        abort_unless(auth()->user()?->can('catalog.coupons.create'), 403);

        $this->form->reset();

        if (! $this->form->isSuperAdmin()) {
            $this->form->business_id = auth()->user()->businessIds()[0] ?? null;
        }

        $this->showModal = true;
        $this->resetValidation();
    }

    #[On('open-coupon-edit')]
    public function openEdit(int $id): void
    {
        abort_unless(auth()->user()?->can('catalog.coupons.edit'), 403);

        $coupon = Coupon::query()->forAuthUser()->findOrFail($id);
        abort_unless($coupon->isEditableBy(), 403);

        $this->form->setCoupon($coupon);
        $this->showModal = true;
        $this->resetValidation();
    }

    #[On('coupon-deleted')]
    public function onRecordDeleted(): void {}

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->form->reset();
        $this->resetValidation();
    }

    public function save(): void
    {
        abort_unless(
            auth()->user()?->can($this->form->isEditing() ? 'catalog.coupons.edit' : 'catalog.coupons.create'),
            403
        );

        $was_editing = $this->form->isEditing();

        CreateOrUpdateCouponAction::run($this->form->coupon_id, $this->form->validated());

        $this->closeModal();

        $this->dispatch('swal', [
            'title' => $was_editing ? 'Cupón actualizado' : 'Cupón creado',
            'icon'  => 'success',
        ]);

        $this->dispatch('coupon-saved');
    }

    public function render()
    {
        $coupons = Coupon::query()->forAuthUser();

        return view('livewire.admin.catalog.coupons.index', [
            'is_super_admin' => $this->form->isSuperAdmin(),
            'businesses'     => $this->form->getBusinesses(),
            'can_create'     => auth()->user()->can('catalog.coupons.create'),
            'stats'          => [
                'total'  => (clone $coupons)->count(),
                'active' => (clone $coupons)->active()->count(),
            ],
        ]);
    }
}
