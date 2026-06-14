<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlansSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name'          => 'Plan Básico',
                'description'   => 'Ideal para pequeñas empresas que inician en el sistema.',
                'monthly_price' => 99000,
                'max_users'     => 3,
                'is_active'     => true,
                'sort_order'    => 1,
                'features'      => [
                    'Hasta 3 usuarios',
                    'Dashboard principal',
                    'Reportes básicos',
                    'Soporte por email',
                ],
                'billing_cycles' => [
                    'monthly'    => ['months' => 1,  'discount' => 0],
                    'quarterly'  => ['months' => 3,  'discount' => 5],
                    'semiannual' => ['months' => 6,  'discount' => 10],
                    'annual'     => ['months' => 12, 'discount' => 15],
                ],
            ],
            [
                'name'          => 'Plan Profesional',
                'description'   => 'Para empresas en crecimiento con necesidades intermedias.',
                'monthly_price' => 249000,
                'max_users'     => 10,
                'is_active'     => true,
                'sort_order'    => 2,
                'features'      => [
                    'Hasta 10 usuarios',
                    'Dashboard avanzado',
                    'Reportes completos + exportación',
                    'Módulos de gestión',
                    'Soporte prioritario por email y chat',
                    'API de integración',
                ],
                'billing_cycles' => [
                    'monthly'    => ['months' => 1,  'discount' => 0],
                    'quarterly'  => ['months' => 3,  'discount' => 5],
                    'semiannual' => ['months' => 6,  'discount' => 12],
                    'annual'     => ['months' => 12, 'discount' => 20],
                ],
            ],
            [
                'name'          => 'Plan Empresarial',
                'description'   => 'Solución completa para grandes empresas sin límites.',
                'monthly_price' => 599000,
                'max_users'     => null,
                'is_active'     => true,
                'sort_order'    => 3,
                'features'      => [
                    'Usuarios ilimitados',
                    'Acceso a todos los módulos',
                    'Reportes avanzados y personalizados',
                    'Carga masiva de datos',
                    'API completa',
                    'Soporte 24/7 dedicado',
                    'Capacitación incluida',
                    'SLA garantizado',
                ],
                'billing_cycles' => [
                    'monthly'    => ['months' => 1,  'discount' => 0],
                    'quarterly'  => ['months' => 3,  'discount' => 5],
                    'semiannual' => ['months' => 6,  'discount' => 12],
                    'annual'     => ['months' => 12, 'discount' => 25],
                ],
            ],
        ];

        foreach ($plans as $data) {
            SubscriptionPlan::updateOrCreate(
                ['name' => $data['name']],
                $data
            );
        }

        $this->command->info('Planes de suscripción creados: Básico ($99k), Profesional ($249k), Empresarial ($599k)');
    }
}
