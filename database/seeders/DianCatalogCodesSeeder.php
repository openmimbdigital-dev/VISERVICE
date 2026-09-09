<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Country;
use App\Models\Unit;
use Illuminate\Database\Seeder;

/**
 * Códigos oficiales que la DIAN exige en el documento electrónico.
 *
 * Vivían solo en la migración que agregó las columnas, que rellenaba las filas
 * existentes. Eso funciona al actualizar una base con datos, pero no en una
 * instalación nueva: la migración corre con las tablas vacías y los catálogos
 * se siembran después, así que las ciudades quedaban sin código DANE y el
 * bloque de direcciones salía incompleto.
 *
 * Es idempotente: solo escribe la fila cuyo código no coincide.
 */
class DianCatalogCodesSeeder extends Seeder
{
    /** name => [dane_code, department_code, department_name] */
    private const CITY_CODES = [
        'Bogotá'        => ['11001', '11', 'Bogotá D.C.'],
        'Medellín'      => ['05001', '05', 'Antioquia'],
        'Cali'          => ['76001', '76', 'Valle del Cauca'],
        'Barranquilla'  => ['08001', '08', 'Atlántico'],
        'Cartagena'     => ['13001', '13', 'Bolívar'],
        'Cúcuta'        => ['54001', '54', 'Norte de Santander'],
        'Bucaramanga'   => ['68001', '68', 'Santander'],
        'Pereira'       => ['66001', '66', 'Risaralda'],
        'Santa Marta'   => ['47001', '47', 'Magdalena'],
        'Ibagué'        => ['73001', '73', 'Tolima'],
        'Pasto'         => ['52001', '52', 'Nariño'],
        'Manizales'     => ['17001', '17', 'Caldas'],
        'Neiva'         => ['41001', '41', 'Huila'],
        'Villavicencio' => ['50001', '50', 'Meta'],
        'Armenia'       => ['63001', '63', 'Quindío'],
        'Valledupar'    => ['20001', '20', 'Cesar'],
        'Montería'      => ['23001', '23', 'Córdoba'],
        'Sincelejo'     => ['70001', '70', 'Sucre'],
        'Popayán'       => ['19001', '19', 'Cauca'],
        'Tunja'         => ['15001', '15', 'Boyacá'],
    ];

    /** ISO 3166-1 alpha-3 => alpha-2 (la DIAN usa alpha-2). */
    private const COUNTRY_CODES = [
        'COL' => 'CO', 'USA' => 'US', 'ARG' => 'AR', 'BRA' => 'BR',
        'CHL' => 'CL', 'PER' => 'PE', 'ECU' => 'EC', 'VEN' => 'VE',
        'URY' => 'UY', 'PRY' => 'PY', 'BOL' => 'BO',
    ];

    /** Unidad del catálogo => código UN/ECE Rec. 20 exigido por la DIAN. */
    private const UNIT_CODES = [
        'Kilogramo'  => 'KGM',
        'Gramo'      => 'GRM',
        'Miligramo'  => 'MGM',
        'Litro'      => 'LTR',
        'Mililitro'  => 'MLT',
        'Metro'      => 'MTR',
        'Centímetro' => 'CMT',
        'Milímetro'  => 'MMT',
        'Unidad'     => 'NIU',
        'Galón'      => 'GLL',
    ];

    public function run(): void
    {
        $cities = 0;

        foreach (self::CITY_CODES as $name => [$dane_code, $department_code, $department_name]) {
            $cities += City::query()
                ->where('name', $name)
                ->where(function ($query) use ($dane_code) {
                    $query->whereNull('dane_code')->orWhere('dane_code', '<>', $dane_code);
                })
                ->update([
                    'dane_code'       => $dane_code,
                    'department_code' => $department_code,
                    'department_name' => $department_name,
                ]);
        }

        $countries = 0;

        foreach (self::COUNTRY_CODES as $iso3 => $iso2) {
            $countries += Country::query()
                ->where('code', $iso3)
                ->where(function ($query) use ($iso2) {
                    $query->whereNull('iso_alpha2')->orWhere('iso_alpha2', '<>', $iso2);
                })
                ->update(['iso_alpha2' => $iso2]);
        }

        $units = 0;

        foreach (self::UNIT_CODES as $name => $unece_code) {
            $units += Unit::query()
                ->where('name', $name)
                ->where(function ($query) use ($unece_code) {
                    $query->whereNull('unece_code')->orWhere('unece_code', '<>', $unece_code);
                })
                ->update(['unece_code' => $unece_code]);
        }

        $this->command?->info("Códigos DIAN: {$cities} ciudad(es), {$countries} país(es), {$units} unidad(es).");
    }
}
