<?php

namespace App\Livewire\Public\Participants\Portal;

use App\Models\Event;
use App\Support\Public\BusinessPublicId;
use App\Support\Public\ParticipantsPortalAuthorization;
use App\Support\Public\ParticipantsPortalSession;
use App\Support\Public\PublicRouteAccess;
use Livewire\Component;

class EventShow extends Component
{
    public string $business_token = '';

    public string $business_name = '';

    /** @var list<array{key: string, label: string, route_name: string, url: string|null}> */
    public array $portal_items = [];

    public Event $event;

    public function mount(string $businessToken, Event $event): void
    {
        $business = BusinessPublicId::resolveBusiness($businessToken);

        abort_unless($business !== null, 404);

        if (! ParticipantsPortalSession::isAuthenticated($business)) {
            $this->redirectRoute('public.participants.access', ['businessToken' => $businessToken], navigate: true);

            return;
        }

        abort_unless(
            PublicRouteAccess::businessAllowsItem($business, ParticipantsPortalAuthorization::EVENTS_ITEM),
            404
        );

        abort_unless((int) $event->business_id === (int) $business->id, 404);
        abort_unless(! $event->multi_day && $event->active, 404);

        $this->event = $event->loadMissing([
            'business:id,name,organization_type_id',
            'business.organization_type:id,label',
            'category:id,name,type',
            'teams:id,name',
            'parent:id,name,date_start,date_end',
        ]);

        $this->business_token = $businessToken;
        $this->business_name = $business->name;
        $this->portal_items = PublicRouteAccess::portalItemsForBusiness($business, $businessToken);
    }

    public function render()
    {
        return view('livewire.public.participants.portal.event-show')
            ->layout('layouts.public-portal', [
                'title' => $this->event->name,
                'business_name' => $this->business_name,
                'home_url' => route('public.participants.home', ['businessToken' => $this->business_token]),
                'portal_items' => $this->portal_items,
                'active_nav' => 'public.participants.events',
                'content_max_width' => 'max-w-[90rem]',
            ]);
    }
}
