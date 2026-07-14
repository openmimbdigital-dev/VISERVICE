<?php

namespace App\Livewire\Admin\Catalog\Products;

use App\Models\Product;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Productos')]
class Index extends Component
{
    #[On('product-deleted')]
    #[On('product-saved')]
    public function onRecordChanged(): void {}

    public function mount(): void
    {
        abort_unless(auth()->user()->can('catalog.products.view'), 403);
    }

    public function render()
    {
        $stats = [
            'total'  => Product::query()->forAuthUser()->count(),
            'active' => Product::query()->forAuthUser()->where('status', true)->count(),
        ];

        return view('livewire.admin.catalog.products.index', compact('stats'));
    }
}
