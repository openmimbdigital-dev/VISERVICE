<?php

namespace App\Livewire\Forms\Admin\Catalog;

use App\Models\Business;
use App\Models\Coupon;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Form;

class CouponForm extends Form
{
    public ?int $coupon_id = null;

    public ?int $business_id = null;

    public string $code = '';

    public string $name = '';

    public string $discount_type = Coupon::DISCOUNT_PERCENTAGE;

    public string $discount_value = '';

    public string $min_order_amount = '';

    public string $starts_at = '';

    public string $ends_at = '';

    public string $max_uses = '';

    public bool $active = true;

    public function setCoupon(Coupon $coupon): void
    {
        $this->coupon_id        = $coupon->id;
        $this->business_id      = $coupon->business_id;
        $this->code             = $coupon->code;
        $this->name             = $coupon->name ?? '';
        $this->discount_type    = $coupon->discount_type;
        $this->discount_value   = (string) $coupon->discount_value;
        $this->min_order_amount = $coupon->min_order_amount !== null ? (string) $coupon->min_order_amount : '';
        $this->starts_at        = $coupon->starts_at?->format('Y-m-d') ?? '';
        $this->ends_at          = $coupon->ends_at?->format('Y-m-d') ?? '';
        $this->max_uses         = $coupon->max_uses !== null ? (string) $coupon->max_uses : '';
        $this->active           = (bool) $coupon->active;
    }

    public function reset(...$properties): void
    {
        parent::reset(...$properties);

        $this->coupon_id        = null;
        $this->business_id      = null;
        $this->code             = '';
        $this->name             = '';
        $this->discount_type    = Coupon::DISCOUNT_PERCENTAGE;
        $this->discount_value   = '';
        $this->min_order_amount = '';
        $this->starts_at        = '';
        $this->ends_at          = '';
        $this->max_uses         = '';
        $this->active           = true;
    }

    public function isEditing(): bool
    {
        return (bool) $this->coupon_id;
    }

    public function isSuperAdmin(): bool
    {
        return auth()->user()?->hasRole('superAdmin') ?? false;
    }

    public function resolvedBusinessId(): int
    {
        if ($this->isSuperAdmin()) {
            return (int) $this->business_id;
        }

        return (int) (auth()->user()->businessIds()[0] ?? 0);
    }

    /** @return Collection<int, Business> */
    public function getBusinesses(): Collection
    {
        if (! $this->isSuperAdmin()) {
            return collect();
        }

        return Business::query()->where('status', true)->orderBy('name')->get(['id', 'name']);
    }

    public function rules(): array
    {
        $business_id = $this->resolvedBusinessId();
        $is_percentage = $this->discount_type === Coupon::DISCOUNT_PERCENTAGE;

        return [
            'business_id' => [
                $this->isSuperAdmin() ? 'required' : 'nullable',
                'integer',
                Rule::exists('businesses', 'id'),
            ],
            'code' => [
                'required', 'string', 'max:40', 'regex:/^[A-Za-z0-9\-_]+$/',
                Rule::unique('coupons', 'code')
                    ->where(fn ($query) => $query->where('business_id', $business_id)->whereNull('deleted_at'))
                    ->ignore($this->coupon_id),
            ],
            'name'           => ['nullable', 'string', 'max:120'],
            'discount_type'  => ['required', Rule::in([Coupon::DISCOUNT_PERCENTAGE, Coupon::DISCOUNT_AMOUNT])],
            // Un porcentaje no puede pasar de 100; un valor fijo no tiene tope aquí
            // porque el descuento se recorta al subtotal de la OT al aplicarlo.
            'discount_value' => array_filter([
                'required', 'numeric', 'gt:0',
                $is_percentage ? 'max:100' : null,
            ]),
            'min_order_amount' => ['nullable', 'numeric', 'min:0'],
            'starts_at'        => ['nullable', 'date'],
            'ends_at'          => ['nullable', 'date', 'after_or_equal:starts_at'],
            'max_uses'         => ['nullable', 'integer', 'min:1'],
            'active'           => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'business_id.required'    => 'Selecciona el negocio dueño del cupón.',
            'code.required'           => 'Escribe el código del cupón.',
            'code.regex'              => 'El código solo admite letras, números, guiones y guiones bajos.',
            'code.unique'             => 'Ya existe un cupón con ese código en este negocio.',
            'discount_type.required'  => 'Indica si el descuento es por porcentaje o por valor.',
            'discount_value.required' => 'Escribe el valor del descuento.',
            'discount_value.gt'       => 'El descuento debe ser mayor que cero.',
            'discount_value.max'      => 'Un descuento porcentual no puede superar el 100%.',
            'min_order_amount.min'    => 'El monto mínimo no puede ser negativo.',
            'ends_at.after_or_equal'  => 'La fecha final no puede ser anterior a la inicial.',
            'max_uses.min'            => 'El límite de usos debe ser al menos 1.',
        ];
    }

    /** @return array<string, mixed> */
    public function validated(): array
    {
        $this->validate();

        return [
            'business_id'      => $this->resolvedBusinessId(),
            'code'             => Coupon::normalizeCode($this->code),
            'name'             => $this->name !== '' ? $this->name : null,
            'discount_type'    => $this->discount_type,
            'discount_value'   => (float) $this->discount_value,
            'min_order_amount' => $this->min_order_amount !== '' ? (float) $this->min_order_amount : null,
            'starts_at'        => $this->starts_at !== '' ? $this->starts_at : null,
            'ends_at'          => $this->ends_at !== '' ? $this->ends_at : null,
            'max_uses'         => $this->max_uses !== '' ? (int) $this->max_uses : null,
            'active'           => $this->active,
        ];
    }
}
