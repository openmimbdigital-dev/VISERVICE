<?php

namespace App\Livewire\Admin\Businesses;

use App\Actions\Business\CreateBusinessOwnerAction;
use App\Actions\Business\CreateOrUpdateBusinessAction;
use App\Actions\Subscriptions\ConfirmSubscriptionPaymentAction;
use App\Actions\Subscriptions\CreateSubscriptionWithInvoiceAction;
use App\Livewire\Forms\Admin\Businesses\BusinessForm;
use App\Models\Business;
use App\Models\City;
use App\Models\BusinessType;
use App\Models\SubscriptionPlan;
use App\Support\PaymentProofStorage;
use Illuminate\Support\Facades\DB;
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

    /*
    |--------------------------------------------------------------------------
    | Alta completa (solo al crear)
    |--------------------------------------------------------------------------
    |
    | Un negocio creado desde aquí tiene que nacer igual que uno que se registra
    | solo: con su administrador, su plan y su cobro. Si no, queda un negocio al
    | que nadie puede entrar y que no le debe nada a nadie.
    |
    */

    public string $owner_first_name = '';

    public string $owner_last_name = '';

    public string $owner_email = '';

    public string $owner_username = '';

    public string $owner_password = '';

    public string $owner_phone = '';

    public ?int $plan_id = null;

    public string $billing_cycle = 'monthly';

    public string $payment_type = 'transfer';

    public string $payment_reference = '';

    public $payment_proof = null;

    /** Dar el pago por recibido y generar de una vez la orden con su factura. */
    public bool $register_payment = false;

    /** Resumen de lo que se creó, para contarlo al terminar. */
    public string $creation_notice = '';

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

    /** Sugiere el dígito de verificación del NIT según el algoritmo de la DIAN. */
    public function updatedFormNit(): void
    {
        $this->form->verification_digit = (string) (\App\Support\DianNit::verificationDigit($this->form->nit) ?? '');
    }

    /**
     * Lo que se le pide a un negocio nuevo, además de sus propios datos.
     *
     * @return array<string, mixed>
     */
    private function onboardingRules(): array
    {
        $rules = [
            'owner_first_name'  => 'required|string|max:100',
            'owner_last_name'   => 'required|string|max:100',
            'owner_email'       => 'required|email|max:150|unique:users,email',
            'owner_username'    => 'required|string|max:50|unique:users,username',
            'owner_password'    => 'required|string|min:8',
            'owner_phone'       => 'nullable|string|max:30',
            'plan_id'           => 'required|exists:subscription_plans,id',
            'billing_cycle'     => 'required|in:monthly,quarterly,semiannual,annual',
            'payment_type'      => 'required|in:transfer,cash,online',
            'payment_reference' => 'nullable|string|max:120',
        ];

        // A diferencia del registro público, aquí el comprobante no se exige:
        // quien da de alta el negocio puede tener el respaldo por otra vía. Si
        // adjunta uno, se valida igual.
        if ($this->payment_proof) {
            $rules['payment_proof'] = 'file|mimes:jpg,jpeg,png,pdf|max:5120';
        }

        return $rules;
    }

    /** @return array<string, string> */
    private function onboardingMessages(): array
    {
        return [
            'owner_first_name.required' => 'El administrador necesita un nombre.',
            'owner_last_name.required'  => 'El administrador necesita un apellido.',
            'owner_email.required'      => 'El administrador necesita un correo para entrar.',
            'owner_email.unique'        => 'Ya hay un usuario con ese correo.',
            'owner_username.required'   => 'Define un usuario para el administrador.',
            'owner_username.unique'     => 'Ese nombre de usuario ya está tomado.',
            'owner_password.required'   => 'Define una contraseña inicial.',
            'owner_password.min'        => 'La contraseña debe tener al menos 8 caracteres.',
            'plan_id.required'          => 'Elige el plan al que se suscribe el negocio.',
            'payment_type.required'     => 'Elige cómo va a pagar el negocio.',
            'payment_proof.mimes'       => 'El comprobante debe ser JPG, PNG o PDF.',
            'payment_proof.max'         => 'El comprobante no debe superar 5 MB.',
        ];
    }

    /**
     * Le da al negocio recién creado lo que le falta para existir de verdad:
     * administrador, suscripción y cobro. Y, si se pidió, da el pago por recibido
     * —que es lo que genera la orden y su factura—.
     */
    private function completeOnboarding(Business $business): void
    {
        CreateBusinessOwnerAction::run($business, [
            'first_name'   => $this->owner_first_name,
            'last_name'    => $this->owner_last_name,
            'email'        => $this->owner_email,
            'username'     => $this->owner_username,
            'password'     => $this->owner_password,
            'phone_number' => $this->owner_phone,
        ]);

        $proof_path = $this->payment_proof
            ? PaymentProofStorage::store($business->id, $this->payment_proof)
            : null;

        $invoice = CreateSubscriptionWithInvoiceAction::run(
            business: $business,
            plan: SubscriptionPlan::findOrFail($this->plan_id),
            billing_cycle: $this->billing_cycle,
            payment_method: $this->payment_type,
            payment_proof_path: $proof_path,
            payment_reference: $this->payment_reference ?: null,
            notes: 'Alta desde el panel',
        );

        $this->creation_notice = "Cobro {$invoice->invoice_number} pendiente de pago.";

        if (! $this->register_payment) {
            return;
        }

        $work_order_invoice = ConfirmSubscriptionPaymentAction::run(
            invoice: $invoice,
            payment_method: $this->payment_type,
            payment_reference: $this->payment_reference ?: null,
            notes: 'Pago registrado al dar de alta el negocio desde el panel.',
        );

        $this->creation_notice = $work_order_invoice
            ? "Cobro {$invoice->invoice_number} pagado; se generó la orden y su factura {$work_order_invoice->reference}."
            : "Cobro {$invoice->invoice_number} pagado.";
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

        $is_new = ! $this->form->isEditing();
        $business_data = $this->form->validated();

        if ($is_new) {
            $this->validate($this->onboardingRules(), $this->onboardingMessages());
        }

        // El negocio, su administrador y su cobro nacen juntos o no nacen: a
        // medias quedaría un negocio en el que nadie puede entrar.
        DB::transaction(function () use ($business_data, $update_status, $is_new) {
            $business = CreateOrUpdateBusinessAction::run(
                $this->form->business_id,
                $business_data,
                $this->new_logo,
                $this->remove_logo,
                $update_status
            );

            if ($is_new) {
                $this->completeOnboarding($business);
            }
        });

        $this->dispatch('swal', [
            'title' => $is_new ? 'Negocio creado.' : 'Negocio actualizado.',
            'text'  => $this->creation_notice ?: null,
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
            'business_types'      => BusinessType::query()
                ->where('active', true)
                ->orderBy('name')
                ->get(),
            'cities'              => City::query()->where('is_active', true)->orderBy('name')->get(),
            'plans'               => SubscriptionPlan::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get(),
            'selected_plan'       => $this->plan_id
                ? SubscriptionPlan::query()->find($this->plan_id)
                : null,
            'can_edit_status'     => $this->canEditStatus(),
        ]);
    }
}
