<?php

namespace App\Livewire\Public\Participants\Portal;

use App\Enums\DocumentType;
use App\Models\Business;
use App\Support\Public\BusinessPublicId;
use App\Support\Public\ParticipantsPortalSession;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.public')]
class Gate extends Component
{
    public string $business_token = '';

    public string $business_name = '';

    public ?Business $business = null;

    public string $pin = '';

    public string $document_type = '';

    public string $document_number = '';

    public bool $pin_not_configured = false;

    public function mount(string $businessToken): void
    {
        $business = $this->resolveBusiness($businessToken);

        if (ParticipantsPortalSession::isAuthenticated($business)) {
            $this->redirectRoute('public.participants.home', ['businessToken' => $businessToken], navigate: true);

            return;
        }

        $this->pin_not_configured = ! ParticipantsPortalSession::pinConfigured($business);

        $this->business = $business;
        $this->business_token = $businessToken;
        $this->business_name = $business->name;
    }

    public function authenticate(): void
    {
        $this->validate([
            'pin' => ['required', 'digits:6'],
            'document_type' => ['required', 'string', 'in:'.implode(',', array_column(DocumentType::cases(), 'value'))],
            'document_number' => ['required', 'string', 'max:30'],
        ], [
            'pin.required' => 'El PIN es obligatorio.',
            'pin.digits' => 'El PIN debe tener exactamente 6 dígitos.',
            'document_type.required' => 'El tipo de documento es obligatorio.',
            'document_type.in' => 'El tipo de documento no es válido.',
            'document_number.required' => 'El número de documento es obligatorio.',
            'document_number.max' => 'El número de documento no puede superar 30 caracteres.',
        ]);

        $business = $this->resolveBusiness($this->business_token);

        $participant = ParticipantsPortalSession::authenticate(
            $business,
            $this->pin,
            $this->document_type,
            trim($this->document_number),
        );

        if ($participant === null) {
            $this->addError('pin', 'PIN, tipo o número de documento incorrectos.');
            $this->reset('pin');

            return;
        }

        $this->redirectRoute('public.participants.home', ['businessToken' => $this->business_token], navigate: true);
    }

    public function render()
    {
        return view('livewire.public.participants.portal.gate', [
            'document_types' => DocumentType::options(),
        ])->layoutData([
            'title' => 'Acceso al portal — '.$this->business_name,
            'business_name' => $this->business_name,
            'portal_business' => $this->business,
        ]);
    }

    private function resolveBusiness(string $token): Business
    {
        $business = BusinessPublicId::resolveBusiness($token);

        abort_unless($business !== null, 404);

        return $business;
    }
}
