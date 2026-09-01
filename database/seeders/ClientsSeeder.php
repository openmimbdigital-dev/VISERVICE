<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\City;
use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Seeder;

class ClientsSeeder extends Seeder
{
    public function run(): void
    {
        $city_ids = City::query()->whereIn('code', ['BOG', 'MED', 'CAL'])->pluck('id', 'code');

        $businesses = [
            'transportes-transad' => [
                'created_by_username' => 'admin',
                'city_code'           => 'BOG',
                'clients'             => [
                    [
                        'name'            => 'Transportes Andinos Ltda.',
                        'document_type'   => 'NIT',
                        'document_number' => '900111222-3',
                        'phone'           => '+57 300 111 2233',
                        'email'           => 'contacto@andinos.com',
                        'address'         => 'Autopista Norte # 145-20, Bogotá',
                        'contact_name'    => 'Luis Fernando Gómez',
                        'status'          => true,
                        'notes'           => 'Flota de 12 camiones; mantenimiento preventivo mensual.',
                    ],
                    [
                        'name'            => 'María Fernanda Ríos',
                        'document_type'   => 'CC',
                        'document_number' => '52345678',
                        'phone'           => '+57 310 222 3344',
                        'email'           => 'mrios@email.com',
                        'address'         => 'Calle 80 # 12-45, Bogotá',
                        'contact_name'    => null,
                        'status'          => true,
                        'notes'           => null,
                    ],
                    [
                        'name'            => 'Distribuidora El Camino S.A.S',
                        'document_type'   => 'NIT',
                        'document_number' => '900222333-4',
                        'phone'           => '+57 601 333 4455',
                        'email'           => 'logistica@elcamino.co',
                        'address'         => 'Zona Industrial Puente Aranda, Bogotá',
                        'contact_name'    => 'Pedro Andrés Mejía',
                        'status'          => true,
                        'notes'           => 'Cliente corporativo con contrato de servicio.',
                    ],
                    [
                        'name'            => 'Jorge Iván Castañeda',
                        'document_type'   => 'CC',
                        'document_number' => '79456123',
                        'phone'           => '+57 320 444 5566',
                        'email'           => 'jcastaneda@email.com',
                        'address'         => 'Carrera 15 # 93-47, Bogotá',
                        'contact_name'    => null,
                        'status'          => true,
                        'notes'           => 'Propietario de tractomula Kenworth.',
                    ],
                    [
                        'name'            => 'Agroexportadora La Sabana',
                        'document_type'   => 'NIT',
                        'document_number' => '900333444-5',
                        'phone'           => '+57 300 555 6677',
                        'email'           => 'operaciones@lasabana.com',
                        'address'         => 'Km 2 vía Siberia, Cota',
                        'contact_name'    => 'Ana Lucía Herrera',
                        'status'          => false,
                        'notes'           => 'Inactivo temporalmente por reestructuración de flota.',
                    ],
                ],
            ],
            'carga-rapida-sas' => [
                'created_by_username' => 'operador',
                'city_code'           => 'MED',
                'clients'             => [
                    [
                        'name'            => 'Logística Express Medellín',
                        'document_type'   => 'NIT',
                        'document_number' => '900444555-6',
                        'phone'           => '+57 304 666 7788',
                        'email'           => 'flota@logexpress.co',
                        'address'         => 'Carrera 43A # 1-50, Medellín',
                        'contact_name'    => 'Ricardo Sánchez',
                        'status'          => true,
                        'notes'           => null,
                    ],
                    [
                        'name'            => 'Carolina Restrepo Vélez',
                        'document_type'   => 'CC',
                        'document_number' => '43567890',
                        'phone'           => '+57 311 777 8899',
                        'email'           => 'crestrepo@email.com',
                        'address'         => 'Envigado, Antioquia',
                        'contact_name'    => null,
                        'status'          => true,
                        'notes'           => 'Camioneta NPR para reparto urbano.',
                    ],
                    [
                        'name'            => 'Minera del Norte S.A.',
                        'document_type'   => 'NIT',
                        'document_number' => '900555666-7',
                        'phone'           => '+57 604 888 9900',
                        'email'           => 'mantenimiento@mineranorte.com',
                        'address'         => 'Zona Franca Rionegro',
                        'contact_name'    => 'Diego Alejandro Muñoz',
                        'status'          => true,
                        'notes'           => 'Mantenimiento de maquinaria pesada.',
                    ],
                    [
                        'name'            => 'Óscar Eduardo Giraldo',
                        'document_type'   => 'CC',
                        'document_number' => '71234567',
                        'phone'           => '+57 315 999 0011',
                        'email'           => null,
                        'address'         => 'Bello, Antioquia',
                        'contact_name'    => null,
                        'status'          => true,
                        'notes'           => null,
                    ],
                    [
                        'name'            => 'Fletes Unidos del Aburrá',
                        'document_type'   => 'NIT',
                        'document_number' => '900666777-8',
                        'phone'           => '+57 300 101 2020',
                        'email'           => 'admin@fletesunidos.co',
                        'address'         => 'Itagüí, Antioquia',
                        'contact_name'    => 'Sandra Milena Ospina',
                        'status'          => true,
                        'notes'           => 'Flota mixta de 8 vehículos.',
                    ],
                ],
            ],
            'transportes-del-valle' => [
                'created_by_username' => 'admin.valle',
                'city_code'           => 'CAL',
                'clients'             => [
                    [
                        'name'            => 'Cafexportadores del Pacífico',
                        'document_type'   => 'NIT',
                        'document_number' => '900777888-9',
                        'phone'           => '+57 302 303 4040',
                        'email'           => 'logistica@cafexport.com',
                        'address'         => 'Yumbo, Valle del Cauca',
                        'contact_name'    => 'Mauricio Valencia',
                        'status'          => true,
                        'notes'           => 'Transporte de carga refrigerada.',
                    ],
                    [
                        'name'            => 'Patricia Elena Muñoz',
                        'document_type'   => 'CC',
                        'document_number' => '31456789',
                        'phone'           => '+57 318 505 6060',
                        'email'           => 'pmunoz@email.com',
                        'address'         => 'Calle 5 # 23-10, Cali',
                        'contact_name'    => null,
                        'status'          => true,
                        'notes'           => null,
                    ],
                    [
                        'name'            => 'Industrias del Sur Ltda.',
                        'document_type'   => 'NIT',
                        'document_number' => '900888999-0',
                        'phone'           => '+57 602 707 8080',
                        'email'           => 'compras@indsur.com',
                        'address'         => 'Parque Industrial Jamundí',
                        'contact_name'    => 'Héctor Fabio Rentería',
                        'status'          => true,
                        'notes'           => 'Cliente desde 2024.',
                    ],
                    [
                        'name'            => 'Andrés Felipe Quintero',
                        'document_type'   => 'CC',
                        'document_number' => '16890123',
                        'phone'           => '+57 320 909 1010',
                        'email'           => 'aquintero@email.com',
                        'address'         => 'Palmira, Valle del Cauca',
                        'contact_name'    => null,
                        'status'          => true,
                        'notes'           => 'Motocicleta y camioneta de reparto.',
                    ],
                    [
                        'name'            => 'Cooperativa de Transportadores del Valle',
                        'document_type'   => 'NIT',
                        'document_number' => '900999000-1',
                        'phone'           => '+57 300 111 3131',
                        'email'           => 'cooperativa@cotraval.co',
                        'address'         => 'Carrera 1 # 18-50, Cali',
                        'contact_name'    => 'Gloria Stella Naranjo',
                        'status'          => false,
                        'notes'           => 'Pendiente actualización de documentación.',
                    ],
                ],
            ],
        ];

        $created = 0;

        foreach ($businesses as $slug => $config) {
            $business = Business::where('slug', $slug)->first();

            if (! $business) {
                $this->command->warn("Negocio no encontrado: {$slug}. Se omiten sus clientes.");

                continue;
            }

            $created_by = User::where('username', $config['created_by_username'])->value('id');
            $city_id    = $city_ids[$config['city_code']] ?? null;

            foreach ($config['clients'] as $client_data) {
                Client::updateOrCreate(
                    [
                        'business_id'     => $business->id,
                        'document_number' => $client_data['document_number'],
                    ],
                    array_merge($client_data, [
                        'business_id' => $business->id,
                        'city_id'     => $city_id,
                        'created_by'  => $created_by,
                    ])
                );

                $created++;
            }
        }

        $this->command->info("Clientes: {$created} registros creados o actualizados.");
    }
}
