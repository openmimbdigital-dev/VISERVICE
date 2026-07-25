<?php

namespace Database\Seeders;

use App\Enums\Weekday;
use App\Models\Business;
use App\Models\Event;
use App\Models\EventCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class EventsSeeder extends Seeder
{
    private const BUSINESS_SLUG = 'centro-de-fe-y-esperanza-sampues';

    public function run(): void
    {
        $business = Business::query()->where('slug', self::BUSINESS_SLUG)->first();

        if (! $business) {
            $this->command?->warn('Eventos: no se encontró el Centro de Fe y Esperanza Sampues.');

            return;
        }

        $categories = EventCategory::query()
            ->where('business_id', $business->id)
            ->orderBy('name')
            ->get();

        if ($categories->isEmpty()) {
            $this->command?->warn('Eventos: no hay categorías asignadas a la iglesia.');

            return;
        }

        $created = 0;
        $base_date = Carbon::now()->startOfDay()->addDays(7);

        foreach ($categories as $index => $category) {
            $date = $base_date->copy()->addWeeks($index);
            $name = $category->name.' — demo';

            Event::withTrashed()->updateOrCreate(
                [
                    'business_id' => $business->id,
                    'event_category_id' => $category->id,
                    'name' => $name,
                ],
                [
                    'description' => $category->description,
                    'date' => $date->toDateString(),
                    'day' => Weekday::labelFromDate($date),
                    'start_time' => '09:00',
                    'end_time' => '11:00',
                    'attendance' => 0,
                    'deleted_at' => null,
                ]
            );

            $created++;
        }

        $this->command?->info("Eventos demo: {$created} registros.");
    }
}
