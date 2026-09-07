<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Códigos DANE de las capitales cargadas en el catálogo.
     * name => [dane_code, department_code, department_name]
     */
    private const CITY_CODES = [
        'Bogotá'       => ['11001', '11', 'Bogotá D.C.'],
        'Medellín'     => ['05001', '05', 'Antioquia'],
        'Cali'         => ['76001', '76', 'Valle del Cauca'],
        'Barranquilla' => ['08001', '08', 'Atlántico'],
        'Cartagena'    => ['13001', '13', 'Bolívar'],
        'Cúcuta'       => ['54001', '54', 'Norte de Santander'],
        'Bucaramanga'  => ['68001', '68', 'Santander'],
        'Pereira'      => ['66001', '66', 'Risaralda'],
        'Santa Marta'  => ['47001', '47', 'Magdalena'],
        'Ibagué'       => ['73001', '73', 'Tolima'],
        'Pasto'        => ['52001', '52', 'Nariño'],
        'Manizales'    => ['17001', '17', 'Caldas'],
        'Neiva'        => ['41001', '41', 'Huila'],
        'Villavicencio' => ['50001', '50', 'Meta'],
        'Armenia'      => ['63001', '63', 'Quindío'],
        'Valledupar'   => ['20001', '20', 'Cesar'],
        'Montería'     => ['23001', '23', 'Córdoba'],
        'Sincelejo'    => ['70001', '70', 'Sucre'],
        'Popayán'      => ['19001', '19', 'Cauca'],
        'Tunja'        => ['15001', '15', 'Boyacá'],
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

    public function up(): void
    {
        Schema::table('cities', function (Blueprint $table) {
            $table->string('dane_code', 5)->nullable()->after('code')
                ->comment('Código DANE del municipio para facturación electrónica');
            $table->string('department_code', 2)->nullable()->after('dane_code')
                ->comment('Código DANE del departamento');
            $table->string('department_name')->nullable()->after('department_code')
                ->comment('Nombre oficial DANE del departamento');

            $table->index('dane_code', 'cities_dane_code_idx');
        });

        Schema::table('countries', function (Blueprint $table) {
            $table->string('iso_alpha2', 2)->nullable()->after('code')
                ->comment('ISO 3166-1 alpha-2 exigido por la DIAN');
        });

        Schema::table('units', function (Blueprint $table) {
            $table->string('unece_code', 10)->default('NIU')->after('symbol')
                ->comment('Código UN/ECE Rec. 20 de unidad de medida (DIAN)');
        });

        foreach (self::CITY_CODES as $name => [$dane_code, $department_code, $department_name]) {
            DB::table('cities')->where('name', $name)->update([
                'dane_code'       => $dane_code,
                'department_code' => $department_code,
                'department_name' => $department_name,
            ]);
        }

        foreach (self::COUNTRY_CODES as $alpha3 => $alpha2) {
            DB::table('countries')->where('code', $alpha3)->update(['iso_alpha2' => $alpha2]);
        }

        foreach (self::UNIT_CODES as $name => $unece_code) {
            DB::table('units')->where('name', $name)->update(['unece_code' => $unece_code]);
        }
    }

    public function down(): void
    {
        Schema::table('cities', function (Blueprint $table) {
            $table->dropIndex('cities_dane_code_idx');
            $table->dropColumn(['dane_code', 'department_code', 'department_name']);
        });

        Schema::table('countries', function (Blueprint $table) {
            $table->dropColumn('iso_alpha2');
        });

        Schema::table('units', function (Blueprint $table) {
            $table->dropColumn('unece_code');
        });
    }
};
