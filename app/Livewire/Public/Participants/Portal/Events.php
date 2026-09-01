<?php

namespace App\Livewire\Public\Participants\Portal;

use App\Models\Business;
use App\Support\Public\BusinessPublicId;
use App\Support\Public\ParticipantsPortalAuthorization;
use App\Support\Public\PublicRouteAccess;
use Livewire\Component;

class Events extends Component
{
    public string $business_token = '';

    public string $business_name = '';

    public ?Business $business = null;

    /** @var list<array{key: string, label: string, route_name: string, url: string|null}> */
    public array $portal_items = [];

    public function mount(string $businessToken): void
    {
        $business = BusinessPublicId::resolveBusiness($businessToken);

        abort_unless($business !== null, 404);

        abort_unless(
            PublicRouteAccess::businessAllowsItem($business, ParticipantsPortalAuthorization::EVENTS_ITEM),
            404
        );

        $this->business_token = $businessToken;
        $this->business_name = $business->name;
        $this->business = $business;
        $this->portal_items = PublicRouteAccess::portalItemsForBusiness($business, $businessToken);
    }

    public function render()
    {
        return view('livewire.public.participants.portal.events', [
            'events_feed_url' => route('public.participants.events.feed', [
                'businessToken' => $this->business_token,
            ]),
        ])->layout('layouts.public-portal', [
            'title' => 'Eventos',
            'business_name' => $this->business_name,
            'portal_business' => $this->business,
            'home_url' => route('public.participants.home', ['businessToken' => $this->business_token]),
            'portal_items' => $this->portal_items,
            'active_nav' => 'public.participants.events',
            'content_max_width' => 'max-w-[90rem]',
        ]);
    }
}
