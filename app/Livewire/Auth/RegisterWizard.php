<?php

namespace App\Livewire\Auth;

use App\Actions\Business\CreateParticipantFromUserAction;
use App\Actions\Business\SyncBusinessAccessFromOrganizationTypeAction;
use App\Models\BankAccount;
use App\Models\Business;
use App\Models\BusinessType;
use App\Models\OrganizationType;
use App\Models\City;
use App\Models\Subscription;
use App\Models\SubscriptionInvoice;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Support\BusinessLogoStorage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.auth')]
#[Title('Crear cuenta — SouulBi')]
class RegisterWizard extends Component
{
    use WithFileUploads;

    public int $step = 1;
    public int $totalSteps = 4;

    // Paso 1 — Datos del Comercio
    public string $business_name = '';
    public ?int $business_type_id = null;
    public string $business_nit = '';
    public string $business_phone = '';
    public string $business_email = '';
    public string $business_address = '';
    public ?int $business_city_id = null;
    public $business_logo = null;

    // Paso 2 — Datos del Usuario
    public string $first_name = '';
    public string $last_name = '';
    public string $email = '';
    public string $username = '';
    public string $password = '';
    public string $password_confirmation = '';
    public string $user_phone = '';

    // Paso 3 — Plan
    public ?int $plan_id = null;
    public string $billing_cycle = 'monthly';

    // Paso 4 — Pago
    public string $payment_type = '';
    public $payment_proof = null;
    public string $payment_reference = '';

    public function nextStep(): void
    {
        $this->validateCurrentStep();
        $this->step++;
    }

    public function prevStep(): void
    {
        if ($this->step > 1) {
            $this->step--;
        }
    }

    public function submit(): void
    {
        $rules = [
            'payment_type' => 'required|in:transfer,cash',
        ];

        if ($this->payment_type === 'transfer') {
            $rules['payment_proof'] = 'required|file|mimes:jpg,jpeg,png,pdf|max:5120';
        }

        $this->validate($rules, [
            'payment_type.required'  => 'Selecciona un método de pago.',
            'payment_type.in'        => 'Método de pago no válido.',
            'payment_proof.required' => 'Adjunta el comprobante de transferencia.',
            'payment_proof.mimes'    => 'El comprobante debe ser JPG, PNG o PDF.',
            'payment_proof.max'      => 'El comprobante no debe superar 5MB.',
        ]);

        DB::transaction(function () {
            // 1. Crear el comercio
            $slug = Str::slug($this->business_name);
            if (Business::where('slug', $slug)->exists()) {
                $slug .= '-' . Str::random(5);
            }

            $business_type = BusinessType::query()->findOrFail($this->business_type_id);

            $business = Business::create([
                'name'                 => $this->business_name,
                'slug'                 => $slug,
                'nit'                  => $this->business_nit,
                'business_type_id'     => $business_type->id,
                'organization_type_id' => $business_type->organization_type_id,
                'phone_number'         => $this->business_phone,
                'email'                => $this->business_email ?: null,
                'address'              => $this->business_address ?: null,
                'city_id'              => $this->business_city_id ?: null,
                'logo'                 => null,
                'status'               => true,
            ]);

            if ($this->business_logo) {
                $logo_path = BusinessLogoStorage::store($business->id, $this->business_logo);
                $business->update(['logo' => $logo_path]);
            }

            // Acceso a roles/permisos y módulos según organization_type (plantilla de negocio existente)
            SyncBusinessAccessFromOrganizationTypeAction::run($business);

            // 2. Crear el usuario
            $user = User::create([
                'first_name'   => $this->first_name,
                'last_name'    => $this->last_name,
                'email'        => $this->email,
                'username'     => $this->username,
                'password'     => Hash::make($this->password),
                'phone_number' => $this->user_phone ?: null,
                'status'       => true,
            ]);

            $user->attachBusiness($business->id, is_primary: true);

            // 3. Asignar rol Comercio
            $user->assignRole('Comercio');

            // 3b. Crear participante del negocio con los mismos datos del admin
            CreateParticipantFromUserAction::run($user, (int) $business->id);

            // 4. Guardar comprobante de pago si es transferencia
            $proofPath = null;
            if ($this->payment_proof) {
                $proofPath = $this->payment_proof->store('payment-proofs', 'public');
            }

            // 5. Crear suscripción en estado pending
            $plan = SubscriptionPlan::findOrFail($this->plan_id);
            $priceData = $plan->getPriceForCycle($this->billing_cycle);
            $startedAt = now()->toDateString();
            $endsAt = Carbon::now()->addMonths($priceData['months'])->toDateString();

            $subscription = Subscription::create([
                'business_id'          => $business->id,
                'subscription_plan_id' => $this->plan_id,
                'status'               => 'pending',
                'billing_cycle'        => $this->billing_cycle,
                'monthly_price'        => $plan->monthly_price,
                'total_price'          => $priceData['total'],
                'discount_percentage'  => $priceData['discount'],
                'started_at'           => $startedAt,
                'ends_at'              => $endsAt,
                'auto_renew'           => true,
                'notes'                => 'Registro vía onboarding',
            ]);

            // 6. Crear factura pendiente
            SubscriptionInvoice::create([
                'subscription_id'      => $subscription->id,
                'business_id'          => $business->id,
                'invoice_number'       => SubscriptionInvoice::generateInvoiceNumber(),
                'amount'               => $subscription->total_price,
                'status'               => 'pending',
                'billing_period_start' => $startedAt,
                'billing_period_end'   => $endsAt,
                'due_date'             => $startedAt,
                'payment_method'       => $this->payment_type,
                'payment_proof'        => $proofPath,
                'payment_reference'    => $this->payment_reference ?: null,
            ]);

            // 7. Iniciar sesión automáticamente
            Auth::login($user);
        });

        $this->redirect(route('pending-activation'), navigate: false);
    }

    private function validateCurrentStep(): void
    {
        match ($this->step) {
            1 => $this->validate([
                'business_name'    => 'required|string|min:3|max:150',
                'business_type_id' => 'required|exists:business_types,id',
                'business_nit'     => 'required|string|max:30|unique:businesses,nit',
                'business_phone'   => 'required|string|max:20',
                'business_email'   => 'nullable|email|max:150',
                'business_address' => 'nullable|string|max:255',
                'business_city_id' => 'nullable|exists:cities,id',
                'business_logo'    => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            ], [
                'business_name.required'    => 'El nombre del comercio es obligatorio.',
                'business_name.min'         => 'El nombre debe tener al menos 3 caracteres.',
                'business_type_id.required' => 'Selecciona el tipo de negocio.',
                'business_nit.required'     => 'El NIT / RUT es obligatorio.',
                'business_nit.unique'       => 'Ya existe un comercio registrado con este NIT.',
                'business_phone.required'   => 'El teléfono del comercio es obligatorio.',
                'business_email.email'      => 'El correo electrónico no es válido.',
                'business_logo.image'      => 'El logo debe ser una imagen.',
                'business_logo.mimes'      => 'El logo debe ser JPG, PNG o WebP.',
                'business_logo.max'        => 'El logo no debe superar 2MB.',
            ]),

            2 => $this->validate([
                'first_name'            => 'required|string|max:100',
                'last_name'             => 'required|string|max:100',
                'email'                 => 'required|email|max:150|unique:users,email',
                'username'              => 'required|string|min:4|max:50|unique:users,username',
                'password'              => 'required|string|min:8|confirmed',
                'password_confirmation' => 'required',
            ], [
                'first_name.required'            => 'El nombre es obligatorio.',
                'last_name.required'             => 'El apellido es obligatorio.',
                'email.required'                 => 'El correo electrónico es obligatorio.',
                'email.email'                    => 'El correo electrónico no es válido.',
                'email.unique'                   => 'Ya existe una cuenta con este correo.',
                'username.required'              => 'El nombre de usuario es obligatorio.',
                'username.min'                   => 'El usuario debe tener al menos 4 caracteres.',
                'username.unique'                => 'Este nombre de usuario ya está en uso.',
                'password.required'              => 'La contraseña es obligatoria.',
                'password.min'                   => 'La contraseña debe tener al menos 8 caracteres.',
                'password.confirmed'             => 'Las contraseñas no coinciden.',
                'password_confirmation.required' => 'Confirma tu contraseña.',
            ]),

            3 => $this->validate([
                'plan_id'       => 'required|exists:subscription_plans,id',
                'billing_cycle' => 'required|in:monthly,quarterly,semiannual,annual',
            ], [
                'plan_id.required'       => 'Selecciona un plan.',
                'billing_cycle.required' => 'Selecciona un ciclo de facturación.',
            ]),

            default => null,
        };
    }

    public function render()
    {
        $plans = SubscriptionPlan::where('is_active', true)->orderBy('sort_order')->get();

        $selectedPlan = $this->plan_id ? $plans->firstWhere('id', $this->plan_id) : null;
        $priceData = ($selectedPlan && $this->billing_cycle)
            ? $selectedPlan->getPriceForCycle($this->billing_cycle)
            : null;

        return view('livewire.auth.register-wizard', [
            'organization_types' => OrganizationType::where('status', true)->orderBy('name')->get(),
            'business_types' => BusinessType::query()
                ->where('active', true)
                ->with('organization_type')
                ->orderBy('name')
                ->get(),
            'cities'         => City::where('is_active', true)->orderBy('name')->get(),
            'plans'          => $plans,
            'bank_accounts'  => BankAccount::where('is_active', true)->get(),
            'selected_plan'  => $selectedPlan,
            'price_data'     => $priceData,
        ]);
    }
}
