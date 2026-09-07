<?php

namespace App\Livewire\Admin\Businesses\DianSettings;

use App\Actions\Business\CreateOrUpdateDianSettingAction;
use App\Actions\Dian\RegisterBusinessWithProviderAction;
use App\Livewire\Forms\Admin\Businesses\DianSettingForm;
use App\Models\BusinessDianSetting;
use App\Services\Dian\DianRequestException;
use App\Services\Dian\TitanioClient;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Facturación electrónica')]
class Index extends Component
{
    public DianSettingForm $form;

    public bool $showModal = false;

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

    /** Crea la empresa emisora en la plataforma del proveedor y guarda el tr_tipo_id. */
    public function registerWithProvider(int $id): void
    {
        abort_unless(auth()->user()?->can('dian_settings.edit'), 403);

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
