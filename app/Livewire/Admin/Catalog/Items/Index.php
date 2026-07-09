<?php

namespace App\Livewire\Admin\Catalog\Items;

use App\Models\Item;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Productos')]
class Index extends Component
{
    #[On('item-deleted')]
    #[On('item-saved')]
    public function onRecordChanged(): void {}

    public function mount(): void
    {
        abort_unless(auth()->user()->can('catalog.items.view'), 403);
    }

    public function render()
    {
        $stats = [
            'total'  => Item::query()->forAuthUser()->count(),
            'active' => Item::query()->forAuthUser()->where('status', true)->count(),
        ];

        return view('livewire.admin.catalog.items.index', compact('stats'));
    }
}
