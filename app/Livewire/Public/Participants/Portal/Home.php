<?php

namespace App\Livewire\Public\Participants\Portal;

use App\Models\Business;
use App\Support\Public\BusinessPublicId;
use App\Support\Public\PublicRouteAccess;
use Livewire\Component;

class Home extends Component
{
    public string $business_token = '';

    public string $business_name = '';

    /** @var list<array{key: string, label: string, route_name: string, url: string|null}> */
    public array $portal_items = [];

    public function mount(string $businessToken): void
    {
        $business = $this->resolveBusiness($businessToken);

        $this->business_token = $businessToken;
        $this->business_name = $business->name;
        $this->portal_items = PublicRouteAccess::portalItemsForBusiness($business, $businessToken);
    }

    public function render()
    {
        return view('livewire.public.participants.portal.home')
            ->layout('layouts.public-portal', [
                'title' => 'Participantes',
                'business_name' => $this->business_name,
                'home_url' => route('public.participants.home', ['businessToken' => $this->business_token]),
                'portal_items' => $this->portal_items,
                'active_nav' => 'home',
            ]);
    }

    private function resolveBusiness(string $token): Business
    {
        $business = BusinessPublicId::resolveBusiness($token);

        abort_unless($business !== null, 404);

        return $business;
    }
}
