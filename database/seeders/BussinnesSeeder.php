<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\BusinessCategory;
use App\Models\BusinessType;
use App\Models\City;
use App\Models\Country;
use App\Models\OrganizationType;
use Illuminate\Database\Seeder;

class BussinnesSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedTransportBusinesses();
        $this->seedChurchBusinesses();

        $this->command->info('Empresas sincronizadas exitosamente.');
    }

    private function seedTransportBusinesses(): void
    {
        $transport_carga     = $this->organizationFields('transporte_de_carga');
        $transport_pasajeros = $this->organizationFields('transporte_de_pasajeros');
        $logistica           = $this->organizationFields('logistica_y_distribucion');
        $mensajeria          = $this->organizationFields('servicios_de_mensajeria');
        $mantenimiento       = $this->organizationFields('mantenimiento_de_vehiculos');

        if (! $transport_carga || ! $transport_pasajeros || ! $logistica || ! $mensajeria || ! $mantenimiento) {
            return;
        }

        $bogota       = City::where('code', 'BOG')->first();
        $medellin     = City::where('code', 'MED')->first();
        $cali         = City::where('code', 'CAL')->first();
        $barranquilla = City::where('code', 'BAQ')->first();
        $cartagena    = City::where('code', 'CTG')->first();
        $colombia     = Country::where('code', 'COL')->first();

        $businesses = [
            [
                'name'             => 'Transportes TRANSAD',
                'address'          => 'Carrera 68 # 25-47',
                'email'            => 'info@transad.com.co',
                'slug'             => 'transportes-transad',
                'nit'              => '900123456-1',
                'logo'             => 'logos/transad-logo.png',
                'website'          => 'https://www.transad.com.co',
                'phone_number'     => '+57 1 234 5678',
                'city_id'          => $bogota?->id,
                'country_id'       => $colombia?->id,
                'business_type_id'     => $transport_carga['business_type_id'],
                'organization_type_id' => $transport_carga['organization_type_id'],
                'representative'   => [
                    'name'            => 'Juan Carlos Pérez',
                    'document_type'   => 'CC',
                    'document_number' => '12345678',
                    'phone'           => '+57 300 123 4567',
                    'email'           => 'jperez@transad.com.co',
                ],
                'configurations' => [
                    'max_vehicles'    => 50,
                    'service_areas'   => ['Bogotá', 'Medellín', 'Cali'],
                    'specializations' => ['Carga pesada', 'Carga refrigerada', 'Carga peligrosa'],
                ],
                'status' => true,
            ],
            [
                'name'             => 'Carga Rápida S.A.S',
                'address'          => 'Calle 25 # 68-90',
                'email'            => 'contacto@cargarapida.com',
                'slug'             => 'carga-rapida-sas',
                'nit'              => '900234567-1',
                'phone_number'     => '+57 1 345 6789',
                'city_id'          => $medellin?->id,
                'country_id'       => $colombia?->id,
                'business_type_id'     => $transport_carga['business_type_id'],
                'organization_type_id' => $transport_carga['organization_type_id'],
                'representative'   => [
                    'name'            => 'María Elena Rodríguez',
                    'document_type'   => 'CC',
                    'document_number' => '23456789',
                    'phone'           => '+57 300 234 5678',
                    'email'           => 'mrodriguez@cargarapida.com',
                ],
                'status' => true,
            ],
            [
                'name'             => 'Transportes del Valle',
                'address'          => 'Avenida 6N # 28-75',
                'email'            => 'info@transvalle.com',
                'slug'             => 'transportes-del-valle',
                'nit'              => '900345678-1',
                'phone_number'     => '+57 2 456 7890',
                'city_id'          => $cali?->id,
                'country_id'       => $colombia?->id,
                'business_type_id'     => $transport_carga['business_type_id'],
                'organization_type_id' => $transport_carga['organization_type_id'],
                'representative'   => [
                    'name'            => 'Carlos Alberto López',
                    'document_type'   => 'CC',
                    'document_number' => '34567890',
                    'phone'           => '+57 300 345 6789',
                    'email'           => 'clopez@transvalle.com',
                ],
                'status' => true,
            ],
            [
                'name'             => 'Flota Magdalena',
                'address'          => 'Carrera 46 # 74-174',
                'email'            => 'ventas@flotamagdalena.com',
                'slug'             => 'flota-magdalena',
                'nit'              => '900456789-1',
                'phone_number'     => '+57 5 567 8901',
                'city_id'          => $barranquilla?->id,
                'country_id'       => $colombia?->id,
                'business_type_id'     => $transport_pasajeros['business_type_id'],
                'organization_type_id' => $transport_pasajeros['organization_type_id'],
                'representative'   => [
                    'name'            => 'Ana Patricia González',
                    'document_type'   => 'CC',
                    'document_number' => '45678901',
                    'phone'           => '+57 300 456 7890',
                    'email'           => 'agonzalez@flotamagdalena.com',
                ],
                'status' => true,
            ],
            [
                'name'             => 'Transportes Caribe',
                'address'          => 'Calle 30 # 8-15',
                'email'            => 'info@transcaribe.com',
                'slug'             => 'transportes-caribe',
                'nit'              => '900567890-1',
                'phone_number'     => '+57 5 678 9012',
                'city_id'          => $cartagena?->id,
                'country_id'       => $colombia?->id,
                'business_type_id'     => $transport_pasajeros['business_type_id'],
                'organization_type_id' => $transport_pasajeros['organization_type_id'],
                'representative'   => [
                    'name'            => 'Roberto Carlos Martínez',
                    'document_type'   => 'CC',
                    'document_number' => '56789012',
                    'phone'           => '+57 300 567 8901',
                    'email'           => 'rmartinez@transcaribe.com',
                ],
                'status' => true,
            ],
            [
                'name'             => 'Logística Integral S.A.S',
                'address'          => 'Carrera 7 # 32-16',
                'email'            => 'servicios@logisticaintegral.com',
                'slug'             => 'logistica-integral-sas',
                'nit'              => '900678901-1',
                'website'          => 'https://www.logisticaintegral.com',
                'phone_number'     => '+57 1 789 0123',
                'city_id'          => $bogota?->id,
                'country_id'       => $colombia?->id,
                'business_type_id'     => $logistica['business_type_id'],
                'organization_type_id' => $logistica['organization_type_id'],
                'representative'   => [
                    'name'            => 'Luis Fernando Silva',
                    'document_type'   => 'CC',
                    'document_number' => '67890123',
                    'phone'           => '+57 300 678 9012',
                    'email'           => 'lsilva@logisticaintegral.com',
                ],
                'configurations' => [
                    'warehouses'      => 3,
                    'service_areas'   => ['Nacional', 'Internacional'],
                    'specializations' => ['Almacenamiento', 'Distribución', 'Cross-docking'],
                ],
                'status' => true,
            ],
            [
                'name'             => 'Mensajería Express',
                'address'          => 'Calle 80 # 11-42',
                'email'            => 'envios@mensajeriaexpress.com',
                'slug'             => 'mensajeria-express',
                'nit'              => '900789012-1',
                'phone_number'     => '+57 1 890 1234',
                'city_id'          => $bogota?->id,
                'country_id'       => $colombia?->id,
                'business_type_id'     => $mensajeria['business_type_id'],
                'organization_type_id' => $mensajeria['organization_type_id'],
                'representative'   => [
                    'name'            => 'Patricia Alejandra Herrera',
                    'document_type'   => 'CC',
                    'document_number' => '78901234',
                    'phone'           => '+57 300 789 0123',
                    'email'           => 'pherrera@mensajeriaexpress.com',
                ],
                'status' => true,
            ],
            [
                'name'             => 'Taller Mecánico El Motor',
                'address'          => 'Carrera 50 # 25-30',
                'email'            => 'servicios@elmotor.com',
                'slug'             => 'taller-mecanico-el-motor',
                'nit'              => '900890123-1',
                'phone_number'     => '+57 1 901 2345',
                'city_id'          => $bogota?->id,
                'country_id'       => $colombia?->id,
                'business_type_id'     => $mantenimiento['business_type_id'],
                'organization_type_id' => $mantenimiento['organization_type_id'],
                'representative'   => [
                    'name'            => 'Miguel Ángel Vargas',
                    'document_type'   => 'CC',
                    'document_number' => '89012345',
                    'phone'           => '+57 300 890 1234',
                    'email'           => 'mvargas@elmotor.com',
                ],
                'status' => true,
            ],
        ];

        foreach ($businesses as $business) {
            Business::query()->updateOrCreate(
                ['slug' => $business['slug']],
                $business
            );
        }
    }

    private function seedChurchBusinesses(): void
    {
        $iglesia_type = BusinessType::where('label', 'iglesia')->first();
        $cat_principal = BusinessCategory::where('label', 'iglesia_principal')->first();
        $cat_hija      = BusinessCategory::where('label', 'iglesia_hija')->first();
        $cat_campo     = BusinessCategory::where('label', 'campo_blanco')->first();

        if (! $iglesia_type || ! $cat_principal || ! $cat_hija || ! $cat_campo) {
            return;
        }

        $org_iglesia      = $this->organizationFields('iglesia');
        $org_congregacion = $this->organizationFields('congregacion');
        $org_comunidad    = $this->organizationFields('comunidad_cristiana');

        if (! $org_iglesia) {
            return;
        }

        $colombia     = Country::where('code', 'COL')->first();
        $bogota       = City::where('code', 'BOG')->first();
        $medellin     = City::where('code', 'MED')->first();
        $cali         = City::where('code', 'CAL')->first();
        $barranquilla = City::where('code', 'BAQ')->first();

        $principals = [
            [
                'name'                 => 'Centro de Fe y Esperanza Sampues',
                'slug'                 => 'centro-de-fe-y-esperanza-sampues',
                'nit'                  => '901100001-1',
                'address'              => 'Calle 10 # 5-20, Sampués, Sucre',
                'email'                => 'contacto@feesperanzasampues.org',
                'phone_number'         => '+57 5 280 1001',
                'city_id'              => $barranquilla?->id,
                'country_id'           => $colombia?->id,
                'business_type_id'     => $iglesia_type->id,
                'organization_type_id' => $org_iglesia['organization_type_id'],
                'business_category_id' => $cat_principal->id,
                'business_id'          => null,
                'status'               => true,
            ],
            [
                'name'                 => 'Iglesia Restauración Bogotá',
                'slug'                 => 'iglesia-restauracion-bogota',
                'nit'                  => '901100002-1',
                'address'              => 'Carrera 15 # 85-40',
                'email'                => 'info@restauracionbogota.org',
                'phone_number'         => '+57 1 612 1002',
                'city_id'              => $bogota?->id,
                'country_id'           => $colombia?->id,
                'business_type_id'     => $iglesia_type->id,
                'organization_type_id' => $org_iglesia['organization_type_id'],
                'business_category_id' => $cat_principal->id,
                'business_id'          => null,
                'status'               => true,
            ],
            [
                'name'                 => 'Comunidad Cristiana El Redentor',
                'slug'                 => 'comunidad-cristiana-el-redentor',
                'nit'                  => '901100003-1',
                'address'              => 'Calle 44 # 75-12',
                'email'                => 'hola@elredentor.org',
                'phone_number'         => '+57 4 321 1003',
                'city_id'              => $medellin?->id,
                'country_id'           => $colombia?->id,
                'business_type_id'     => $iglesia_type->id,
                'organization_type_id' => ($org_comunidad ?? $org_iglesia)['organization_type_id'],
                'business_category_id' => $cat_principal->id,
                'business_id'          => null,
                'status'               => true,
            ],
            [
                'name'                 => 'Templo de Alabanza Cali',
                'slug'                 => 'templo-de-alabanza-cali',
                'nit'                  => '901100004-1',
                'address'              => 'Avenida 5 # 23N-45',
                'email'                => 'alabanza@cali.org',
                'phone_number'         => '+57 2 456 1004',
                'city_id'              => $cali?->id,
                'country_id'           => $colombia?->id,
                'business_type_id'     => $iglesia_type->id,
                'organization_type_id' => $org_iglesia['organization_type_id'],
                'business_category_id' => $cat_principal->id,
                'business_id'          => null,
                'status'               => true,
            ],
        ];

        $parent_ids = [];

        foreach ($principals as $principal) {
            $business = Business::query()->updateOrCreate(
                ['slug' => $principal['slug']],
                $principal
            );

            $parent_ids[$principal['slug']] = $business->id;
        }

        $children = [
            [
                'name'                 => 'Congregación Fe y Vida Sampues',
                'slug'                 => 'congregacion-fe-y-vida-sampues',
                'nit'                  => '901100005-1',
                'address'              => 'Barrio El Progreso, Sampués',
                'email'                => 'feyvida@sampues.org',
                'phone_number'         => '+57 5 280 2001',
                'city_id'              => $barranquilla?->id,
                'country_id'           => $colombia?->id,
                'business_type_id'     => $iglesia_type->id,
                'organization_type_id' => ($org_congregacion ?? $org_iglesia)['organization_type_id'],
                'business_category_id' => $cat_hija->id,
                'parent_slug'          => 'centro-de-fe-y-esperanza-sampues',
                'status'               => true,
            ],
            [
                'name'                 => 'Iglesia Hija La Buena Semilla',
                'slug'                 => 'iglesia-hija-la-buena-semilla',
                'nit'                  => '901100006-1',
                'address'              => 'Calle 127 # 15-30, Suba',
                'email'                => 'semilla@restauracionbogota.org',
                'phone_number'         => '+57 1 612 2002',
                'city_id'              => $bogota?->id,
                'country_id'           => $colombia?->id,
                'business_type_id'     => $iglesia_type->id,
                'organization_type_id' => ($org_congregacion ?? $org_iglesia)['organization_type_id'],
                'business_category_id' => $cat_hija->id,
                'parent_slug'          => 'iglesia-restauracion-bogota',
                'status'               => true,
            ],
            [
                'name'                 => 'Congregación Hija Betel Medellín',
                'slug'                 => 'congregacion-hija-betel-medellin',
                'nit'                  => '901100007-1',
                'address'              => 'Carrera 65 # 45-10, Laureles',
                'email'                => 'betel@elredentor.org',
                'phone_number'         => '+57 4 321 2003',
                'city_id'              => $medellin?->id,
                'country_id'           => $colombia?->id,
                'business_type_id'     => $iglesia_type->id,
                'organization_type_id' => ($org_congregacion ?? $org_iglesia)['organization_type_id'],
                'business_category_id' => $cat_hija->id,
                'parent_slug'          => 'comunidad-cristiana-el-redentor',
                'status'               => true,
            ],
            [
                'name'                 => 'Campo Blanco Sincelejo',
                'slug'                 => 'campo-blanco-sincelejo',
                'nit'                  => '901100008-1',
                'address'              => 'Vereda La Esperanza, Sincelejo',
                'email'                => 'sincelejo@feesperanzasampues.org',
                'phone_number'         => '+57 5 280 3001',
                'city_id'              => $barranquilla?->id,
                'country_id'           => $colombia?->id,
                'business_type_id'     => $iglesia_type->id,
                'organization_type_id' => $org_iglesia['organization_type_id'],
                'business_category_id' => $cat_campo->id,
                'parent_slug'          => 'centro-de-fe-y-esperanza-sampues',
                'status'               => true,
            ],
            [
                'name'                 => 'Campo Blanco Facatativá',
                'slug'                 => 'campo-blanco-facatativa',
                'nit'                  => '901100009-1',
                'address'              => 'Km 3 vía Facatativá - El Rosal',
                'email'                => 'facatativa@restauracionbogota.org',
                'phone_number'         => '+57 1 612 3002',
                'city_id'              => $bogota?->id,
                'country_id'           => $colombia?->id,
                'business_type_id'     => $iglesia_type->id,
                'organization_type_id' => $org_iglesia['organization_type_id'],
                'business_category_id' => $cat_campo->id,
                'parent_slug'          => 'iglesia-restauracion-bogota',
                'status'               => true,
            ],
            [
                'name'                 => 'Campo Blanco Palmira',
                'slug'                 => 'campo-blanco-palmira',
                'nit'                  => '901100010-1',
                'address'              => 'Sector Rozo, Palmira',
                'email'                => 'palmira@alabanza.org',
                'phone_number'         => '+57 2 456 3003',
                'city_id'              => $cali?->id,
                'country_id'           => $colombia?->id,
                'business_type_id'     => $iglesia_type->id,
                'organization_type_id' => $org_iglesia['organization_type_id'],
                'business_category_id' => $cat_campo->id,
                'parent_slug'          => 'templo-de-alabanza-cali',
                'status'               => true,
            ],
        ];

        foreach ($children as $child) {
            $parent_slug = $child['parent_slug'];
            unset($child['parent_slug']);

            $child['business_id'] = $parent_ids[$parent_slug] ?? null;

            Business::query()->updateOrCreate(
                ['slug' => $child['slug']],
                $child
            );
        }
    }

    /** @return array{business_type_id: int, organization_type_id: int}|null */
    private function organizationFields(string $label): ?array
    {
        $organization_type = OrganizationType::query()->where('label', $label)->first();

        if (! $organization_type) {
            return null;
        }

        return [
            'business_type_id'     => $organization_type->business_type_id,
            'organization_type_id' => $organization_type->id,
        ];
    }
}
