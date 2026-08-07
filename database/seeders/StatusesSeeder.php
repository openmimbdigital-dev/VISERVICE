<?php

namespace Database\Seeders;

use App\Models\Status;
use Illuminate\Database\Seeder;

class StatusesSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            [
                'name'  => 'created',
                'label' => 'Creada',
                'type'  => ['quotations', 'work_orders', 'remissions'],
            ],
            [
                'name'  => 'sent',
                'label' => 'Enviada',
                'type'  => ['quotations'],
            ],
            [
                'name'  => 'accepted',
                'label' => 'Aceptada',
                'type'  => ['quotations'],
            ],
            [
                'name'  => 'rejected',
                'label' => 'Rechazada',
                'type'  => ['quotations'],
            ],
            [
                'name'  => 'expired',
                'label' => 'Vencida',
                'type'  => ['quotations'],
            ],
            [
                'name'  => 'in_progress',
                'label' => 'En proceso',
                'type'  => ['work_orders', 'remissions'],
            ],
            [
                'name'  => 'completed',
                'label' => 'Finalizada',
                'type'  => ['work_orders', 'remissions'],
            ],
            [
                'name'  => 'cancelled',
                'label' => 'Cancelada',
                'type'  => ['work_orders', 'remissions'],
            ],
            [
                'name'  => 'confirmed',
                'label' => 'Confirmado',
                'type'  => ['work_order_payments'],
            ],
            [
                'name'  => 'voided',
                'label' => 'Anulado',
                'type'  => ['work_order_payments'],
            ],
        ];

        foreach ($statuses as $status) {
            Status::query()->updateOrCreate(
                ['name' => $status['name']],
                [
                    'label'  => $status['label'],
                    'active' => true,
                    'type'   => $status['type'],
                ]
            );
        }

        $this->command?->info('Statuses: '.count($statuses).' registros.');
    }
}
