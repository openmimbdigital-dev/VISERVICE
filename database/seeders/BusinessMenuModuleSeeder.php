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

        $event_management_section = $sections->firstWhere('slug', 'gestion-eventos');
        $reports_section = $sections->firstWhere('slug', 'reportes');

        $special_items = MenuItem::query()
            ->whereIn('route_name', [
                'admin.settings.events.index',
                'admin.settings.equipment.index',
                'admin.settings.catalog-products.index',
                'admin.events.index',
                'admin.events.teams.index',
                'admin.events.team-roles.index',
                'admin.reports.events.attendance.index',
            ])
            ->get()
            ->keyBy('route_name');

        $special_item_ids = $special_items->pluck('id')->map(fn ($id) => (int) $id)->all();

        $church_only_slugs = ['gestion-eventos', 'reportes'];

        $section_ids = $sections
            ->reject(fn (MenuSection $section) => in_array($section->slug, $church_only_slugs, true))
            ->pluck('id')
            ->all();

        $item_ids = $sections
            ->reject(fn (MenuSection $section) => in_array($section->slug, $church_only_slugs, true))
            ->flatMap(fn ($s) => $s->items->pluck('id'))
            ->reject(fn ($id) => in_array((int) $id, $special_item_ids, true))
            ->all();

        $roots = Business::query()
            ->whereNull('business_id')
            ->whereNull('deleted_at')
            ->with('organization_type:id,label')
            ->get();

        foreach ($roots as $business) {
            $business->menuSections()->syncWithoutDetaching($section_ids);

            if ($item_ids !== []) {
                $business->menuItems()->syncWithoutDetaching($item_ids);
            }

            $events_item = $special_items->get('admin.settings.events.index');
            $equipment_item = $special_items->get('admin.settings.equipment.index');
            $catalog_settings_item = $special_items->get('admin.settings.catalog-products.index');
            $events_index_item = $special_items->get('admin.events.index');
            $event_teams_item = $special_items->get('admin.events.teams.index');
            $event_team_roles_item = $special_items->get('admin.events.team-roles.index');
            $attendance_report_item = $special_items->get('admin.reports.events.attendance.index');

            if ($business->organization_type?->label === 'iglesia') {
                if ($events_item) {
                    $business->menuItems()->syncWithoutDetaching([$events_item->id]);
                }

                $event_management_item_ids = array_filter([
                    $events_index_item?->id,
                    $event_teams_item?->id,
                    $event_team_roles_item?->id,
                ]);

                if ($event_management_section && $event_management_item_ids !== []) {
                    $business->menuSections()->syncWithoutDetaching([$event_management_section->id]);
                    $business->menuItems()->syncWithoutDetaching($event_management_item_ids);
                }

                if ($reports_section && $attendance_report_item) {
                    $business->menuSections()->syncWithoutDetaching([$reports_section->id]);
                    $business->menuItems()->syncWithoutDetaching([$attendance_report_item->id]);
                }

                $business->menuItems()->detach(array_filter([
                    $equipment_item?->id,
                    $catalog_settings_item?->id,
                ]));
            } else {
                if ($events_item) {
                    $business->menuItems()->detach($events_item->id);
                }

                if ($event_management_section) {
                    $business->menuSections()->detach($event_management_section->id);
                }

                if ($reports_section) {
                    $business->menuSections()->detach($reports_section->id);
                }

                $business->menuItems()->detach(array_filter([
                    $events_index_item?->id,
                    $event_teams_item?->id,
                    $event_team_roles_item?->id,
                    $attendance_report_item?->id,
                ]));

                $settings_item_ids = array_filter([
                    $equipment_item?->id,
                    $catalog_settings_item?->id,
                ]);

                if ($settings_item_ids !== []) {
                    $business->menuItems()->syncWithoutDetaching($settings_item_ids);
                }
            }
        }

        $this->command?->info("Módulos de menú asignados a {$roots->count()} negocio(s) raíz.");
    }
}
