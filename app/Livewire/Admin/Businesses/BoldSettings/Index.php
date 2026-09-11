<?php

namespace App\Livewire\Admin\Businesses\BoldSettings;

use App\Models\Business;
use App\Models\BusinessBoldSetting;
use App\Services\Bold\BoldClient;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Throwable;

/**
 * Llaves de Bold de cada negocio, para que cobre sus facturas a su cuenta.
 *
 * Las llaves se guardan cifradas y no se vuelven a mostrar: en el formulario se
 * ve solo el final de la que está guardada, y dejarlo vacío la conserva. Así
 * editar el resto de la configuración no obliga a tener las llaves a mano ni las
 * expone en pantalla.
 */
#[Layout('layouts.app')]
#[Title('Pasarela de pagos')]
class Index extends Component
{
    public bool $showModal = false;

    public ?int $business_id = null;

    public string $identity_key = '';

    public string $secret_key = '';

    public bool $active = true;

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('bold_settings.view'), 403);
    }

    public function openEdit(int $business_id): void
    {
        abort_unless(auth()->user()?->can('bold_settings.edit'), 403);
        abort_unless(Business::query()->forAuthUser()->whereKey($business_id)->exists(), 404);

        $setting = BusinessBoldSetting::query()->where('business_id', $business_id)->first();

        $this->business_id  = $business_id;
        $this->identity_key = '';
        $this->secret_key   = '';
        $this->active       = $setting?->active ?? true;

        $this->resetValidation();
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
    }

    public function save(): void
    {
        abort_unless(auth()->user()?->can('bold_settings.edit'), 403);
        abort_unless(Business::query()->forAuthUser()->whereKey($this->business_id)->exists(), 404);

        $data = $this->validate([
            'identity_key' => ['nullable', 'string', 'max:255'],
            'secret_key'   => ['nullable', 'string', 'max:255'],
            'active'       => ['boolean'],
        ], [
            'identity_key.max' => 'La llave de identidad no parece válida.',
            'secret_key.max'   => 'La llave secreta no parece válida.',
        ]);

        $setting = BusinessBoldSetting::query()->firstOrNew(['business_id' => $this->business_id]);

        // Un campo vacío conserva la llave guardada: es lo que permite entrar a
        // cambiar solo el estado sin tener que volver a pegarlas.
        if (trim($data['identity_key']) !== '') {
            $setting->identity_key = trim($data['identity_key']);
        }

        if (trim($data['secret_key']) !== '') {
            $setting->secret_key = trim($data['secret_key']);
        }

        $setting->active = (bool) $data['active'];
        $setting->save();

        $this->showModal = false;

        $this->dispatch('swal', [
            'title' => 'Llaves guardadas',
            'text'  => $setting->isUsable()
                ? 'Este negocio ya cobra a su propia cuenta de Bold.'
                : 'Sin llave de identidad los cobros siguen saliendo por la cuenta de la plataforma.',
            'icon'  => $setting->isUsable() ? 'success' : 'warning',
        ]);
    }

    /**
     * Comprueba contra Bold que la llave de identidad sirva.
     *
     * Se consultan los métodos de pago habilitados porque es la llamada más
     * barata que exige autenticación: si responde, la llave es buena.
     */
    public function testConnection(int $business_id): void
    {
        abort_unless(auth()->user()?->can('bold_settings.view'), 403);

        $setting = BusinessBoldSetting::query()->where('business_id', $business_id)->first();

        if (! $setting?->isUsable()) {
            $this->dispatch('swal', [
                'title' => 'Este negocio no tiene llave propia',
                'text'  => 'Sus cobros salen por la cuenta de la plataforma.',
                'icon'  => 'info',
            ]);

            return;
        }

        try {
            $methods = BoldClient::forBusiness($setting)->paymentMethods();
        } catch (Throwable $exception) {
            $this->dispatch('swal', [
                'title' => 'Bold no aceptó la llave',
                'text'  => $exception->getMessage(),
                'icon'  => 'error',
            ]);

            return;
        }

        $this->dispatch('swal', [
            'title' => 'La llave funciona',
            'text'  => 'Métodos habilitados: '.(implode(', ', array_keys($methods)) ?: 'ninguno'),
            'icon'  => 'success',
        ]);
    }

    public function render()
    {
        $businesses = Business::query()
            ->forAuthUser()
            ->where('status', true)
            ->orderBy('name')
            ->get();

        $settings = BusinessBoldSetting::query()
            ->whereIn('business_id', $businesses->pluck('id'))
            ->get()
            ->keyBy('business_id');

        return view('livewire.admin.businesses.bold-settings.index', [
            'businesses'     => $businesses,
            'settings'       => $settings,
            'can_edit'       => auth()->user()->can('bold_settings.edit'),
            'platform_ready' => filled(config('bold.identity_key')),
            'webhook_url'    => route('webhooks.bold'),
        ]);
    }
}
