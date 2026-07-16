<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\MenuItem;
use App\Models\MenuSection;
use Illuminate\Database\Seeder;

class BusinessMenuModuleSeeder extends Seeder
{
    public function run(): void
    {
        $sections = MenuSection::query()
            ->where('assignable_to_business', true)
            ->with(['items' => fn ($q) => $q->where('active', true)])
            ->get();

        if ($sections->isEmpty()) {
            return;
        }

        $section_ids = $sections->pluck('id')->all();
        $item_ids    = $sections->flatMap(fn ($s) => $s->items->pluck('id'))->all();

        $roots = Business::query()->whereNull('business_id')->whereNull('deleted_at')->get();

        foreach ($roots as $business) {
            $business->menuSections()->syncWithoutDetaching($section_ids);
            if ($item_ids !== []) {
                $business->menuItems()->syncWithoutDetaching($item_ids);
            }
        }

        $this->command?->info("Módulos de menú asignados a {$roots->count()} negocio(s) raíz.");
    }
}
