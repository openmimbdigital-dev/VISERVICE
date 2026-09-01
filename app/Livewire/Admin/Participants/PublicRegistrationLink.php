<?php

namespace App\Livewire\Admin\Participants;

use App\Models\Business;
use App\Support\CurrentBusiness;
use App\Support\Public\BusinessPublicId;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

#[Layout('layouts.app')]
#[Title('Enlace público — Participantes')]
class PublicRegistrationLink extends Component
{
    public string $public_url = '';

    public string $portal_url = '';

    public string $business_name = '';

    public string $qr_svg = '';

    public string $portal_qr_svg = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('participants.view'), 403);

        $business = $this->resolveBusiness();

        abort_unless($business !== null, 404);

        $token = BusinessPublicId::encode((int) $business->id);

        $this->business_name = $business->name;
        $this->public_url = route('public.participants.register', ['businessToken' => $token]);
        $this->qr_svg = (string) QrCode::size(220)->margin(1)->generate($this->public_url);
        $this->portal_url = route('public.participants.home', ['businessToken' => $token]);
        $this->portal_qr_svg = (string) QrCode::size(220)->margin(1)->generate($this->portal_url);
    }

    private function resolveBusiness(): ?Business
    {
        $user = auth()->user();

        if ($user->hasRole('superAdmin')) {
            $business_id = CurrentBusiness::id() ?? $user->business_id;

            if (! $business_id) {
                return Business::query()
                    ->where('status', true)
                    ->whereNull('deleted_at')
                    ->orderBy('name')
                    ->first();
            }

            return Business::query()
                ->whereKey($business_id)
                ->where('status', true)
                ->whereNull('deleted_at')
                ->first();
        }

        $business_id = CurrentBusiness::id() ?? $user->business_id;

        if (! $business_id || ! $user->belongsToBusiness((int) $business_id)) {
            $business_id = $user->primaryBusiness()?->id ?? $user->businessIds()[0] ?? null;
        }

        if (! $business_id) {
            return null;
        }

        return Business::query()
            ->whereKey($business_id)
            ->where('status', true)
            ->whereNull('deleted_at')
            ->first();
    }

    public function render()
    {
        return view('livewire.admin.participants.public-registration-link');
    }
}
