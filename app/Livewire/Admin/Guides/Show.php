<?php

namespace App\Livewire\Admin\Guides;

use App\Models\Guide;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Show extends Component
{
    public Guide $guide;

    public function mount(Guide $guide): void
    {
        abort_unless(auth()->user()?->hasRole('superAdmin'), 403);

        $this->guide = $guide;
    }

    public function render()
    {
        $siblings = Guide::query()
            ->where('module', $this->guide->module)
            ->whereKeyNot($this->guide->id)
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();

        return view('livewire.admin.guides.show', [
            'rendered' => $this->guide->renderedContent(),
            'headings' => $this->guide->headings(),
            'siblings' => $siblings,
        ])->title($this->guide->title);
    }
}
