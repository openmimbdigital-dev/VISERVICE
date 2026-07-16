<?php

namespace App\Livewire\Admin\Workshop\Quotations;

use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Cotizaciones')]
class Index extends Component
{
    public function mount(): void
    {
        abort_unless(auth()->user()?->can('workshop.quotations.view'), 403);
    }

    #[On('quotation-deleted')]
    public function onRecordDeleted(): void {}

    public function render()
    {
        return view('livewire.admin.workshop.quotations.index');
    }
}
