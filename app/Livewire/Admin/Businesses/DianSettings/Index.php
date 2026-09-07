<?php

namespace App\Livewire\Admin\Businesses\DianSettings;

use App\Actions\Business\CreateOrUpdateDianSettingAction;
use App\Actions\Dian\RegisterBusinessWithProviderAction;
use App\Livewire\Forms\Admin\Businesses\DianSettingForm;
use App\Models\BusinessDianSetting;
use App\Services\Dian\DianRequestException;
use App\Services\Dian\TitanioClient;
use App\Support\ConfirmationAlert;
use Illuminate\Validation\ValidationException;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Facturación electrónica')]
class Index extends Component
{
    use LivewireAlert;

    private const REGISTRATION_CONFIRMED_EVENT = 'dian-registration-confirmed';

    public DianSettingForm $form;

    public bool $showModal = false;

    public ?int $registering_id = null;

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('dian_settings.view'), 403);
    }

    public function openCreate(): void
    {
        abort_unless(auth()->user()?->can('dian_settings.create'), 403);

        $this->form->reset();

        if (! $this->form->isSuperAdmin()) {
            $this->form->business_id = auth()->user()->businessIds()[0] ?? null;
        }

        $this->showModal = true;
        $this->resetValidation();
    }

    public function openEdit(int $id): void
    {
        abort_unless(auth()->user()?->can('dian_settings.edit'), 403);

        $setting = BusinessDianSetting::query()->forAuthUser()->findOrFail($id);
        abort_unless($setting->isEditableBy(null, 'dian_settings.edit'), 403);

        $this->form->setDianSetting($setting);
        $this->showModal = true;
        $this->resetValidation();
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->form->reset();
        $this->resetValidation();
    }

    public function save(): void
    {
        abort_unless(
            auth()->user()?->can($this->form->isEditing() ? 'dian_settings.edit' : 'dian_settings.create'),
            403
        );

        $was_editing = $this->form->isEditing();

        CreateOrUpdateDianSettingAction::run(
            $this->form->dian_setting_id,
            $this->form->validated()
        );

        $this->closeModal();

        $this->dispatch('swal', [
            'title' => $was_editing ? 'Configuración actualizada' : 'Configuración creada',
            'icon'  => 'success',
        ]);
    }

    /** Pide confirmación antes de crear la empresa en la plataforma del proveedor. */
    public function confirmRegistration(int $id): void
    {
        abort_unless(auth()->user()?->can('dian_settings.edit'), 403);
        abort_unless(BusinessDianSetting::query()->forAuthUser()->whereKey($id)->exists(), 404);

        $this->registering_id = $id;

        $this->confirm('¿Registrar el negocio ante el proveedor?', ConfirmationAlert::options(
            on_confirmed: self::REGISTRATION_CONFIRMED_EVENT,
            confirm_text: 'Registrar',
            text: 'Se creará la empresa emisora en la plataforma del proveedor con los datos del negocio.',
        ));
    }

    /** Crea la empresa emisora en la plataforma del proveedor y guarda el tr_tipo_id. */
    #[On(self::REGISTRATION_CONFIRMED_EVENT)]
    public function registerWithProvider(?int $id = null): void
    {
        abort_unless(auth()->user()?->can('dian_settings.edit'), 403);

        $id ??= $this->registering_id;
        $this->registering_id = null;

        if (! $id) {
            return;
        }

        $setting = BusinessDianSetting::query()->forAuthUser()->findOrFail($id);

        try {
            $setting = RegisterBusinessWithProviderAction::run($setting);
        } catch (DianRequestException|ValidationException $exception) {
            $this->dispatch('swal', [
                'title' => 'No se pudo registrar ante el proveedor',
                'text'  => $exception instanceof ValidationException
                    ? collect($exception->errors())->flatten()->first()
                    : $exception->getMessage(),
                'icon'  => 'error',
            ]);

            return;
        }

        $this->dispatch('swal', [
            'title' => 'Empresa registrada ante el proveedor',
            'text'  => 'Perfil de emisión: '.$setting->tr_tipo_id,
            'icon'  => 'success',
        ]);
    }

    /** Verifica que las credenciales del proveedor respondan. */
    public function testConnection(string $environment = 'test'): void
    {
        abort_unless(auth()->user()?->can('dian_settings.view'), 403);

        try {
            TitanioClient::for($environment)->token(force_refresh: true);
        } catch (DianRequestException $exception) {
            $this->dispatch('swal', [
                'title' => 'Sin conexión con el proveedor',
                'text'  => $exception->getMessage(),
                'icon'  => 'error',
            ]);

            return;
        }

        $this->dispatch('swal', [
            'title' => 'Conexión correcta',
            'text'  => 'Las credenciales del proveedor respondieron sin errores.',
            'icon'  => 'success',
        ]);
    }

    public function render()
    {
        $settings = BusinessDianSetting::query()
            ->forAuthUser()
            ->with('business.city')
            ->join('businesses', 'businesses.id', '=', 'business_dian_settings.business_id')
            ->orderBy('businesses.name')
            ->select('business_dian_settings.*')
            ->get();

        return view('livewire.admin.businesses.dian-settings.index', [
            'settings'       => $settings,
            'is_super_admin' => $this->form->isSuperAdmin(),
            'businesses'     => $this->form->getBusinesses(),
            'can_create'     => auth()->user()->can('dian_settings.create'),
            'can_edit'       => auth()->user()->can('dian_settings.edit'),
        ]);
    }
}
