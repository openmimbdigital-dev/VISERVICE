<?php

namespace App\Livewire\Public\Participants;

use App\Actions\Public\RegisterPublicParticipantAction;
use App\Enums\DocumentType;
use App\Livewire\Forms\Public\PublicParticipantRegistrationForm;
use App\Models\Business;
use App\Support\Public\BusinessPublicId;
use App\Support\SuccessAlert;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.public')]
class Register extends Component
{
    public PublicParticipantRegistrationForm $form;

    public string $business_token = '';

    public string $business_name = '';

    public ?Business $business = null;

    public bool $submitted = false;

    public function mount(string $businessToken): void
    {
        $business = BusinessPublicId::resolveBusiness($businessToken);

        abort_unless($business !== null, 404);

        $this->business_token = $businessToken;
        $this->business_name = $business->name;
        $this->business = $business;
        $this->form->setBusinessId((int) $business->id);
    }

    public function save(): void
    {
        $business = BusinessPublicId::resolveBusiness($this->business_token);

        abort_unless($business !== null, 404);

        RegisterPublicParticipantAction::run($business, $this->form->validated());

        $this->submitted = true;
        $this->form->clearInputs();
        $this->resetValidation();

        $this->dispatch('swal', SuccessAlert::options(
            'Registro enviado',
            'Tus datos fueron registrados correctamente.',
        ));
    }

    public function render()
    {
        return view('livewire.public.participants.register', [
            'roles' => $this->form->getRoles(),
            'cities' => $this->form->getCities(),
            'countries' => $this->form->getCountries(),
            'document_types' => DocumentType::options(),
        ])->layoutData([
            'title' => 'Registro de participante — '.$this->business_name,
            'business_name' => $this->business_name,
            'portal_business' => $this->business,
        ]);
    }
}
