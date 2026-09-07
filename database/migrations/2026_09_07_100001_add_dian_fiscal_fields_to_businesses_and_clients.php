<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->string('verification_digit', 1)->nullable()->after('nit')
                ->comment('Dígito de verificación del NIT (DIAN)');
            $table->unsignedTinyInteger('person_type')->default(1)->after('verification_digit')
                ->comment('1 = Persona jurídica, 2 = Persona natural (AdditionalAccountID DIAN)');
            $table->string('fiscal_responsibilities', 60)->default('R-99-PN')->after('person_type')
                ->comment('Responsabilidad fiscal DIAN, ej. R-99-PN, O-13, O-15, O-23, O-47');
            $table->string('postal_code', 10)->nullable()->after('fiscal_responsibilities');
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->string('verification_digit', 1)->nullable()->after('document_number')
                ->comment('Dígito de verificación del NIT (DIAN)');
            $table->unsignedTinyInteger('person_type')->default(2)->after('verification_digit')
                ->comment('1 = Persona jurídica, 2 = Persona natural (AdditionalAccountID DIAN)');
            $table->string('fiscal_responsibilities', 60)->default('R-99-PN')->after('person_type')
                ->comment('Responsabilidad fiscal DIAN, ej. R-99-PN, O-13, O-15, O-23, O-47');
        });

        // Los NIT existentes vienen con el DV pegado ("900123456-1"): se separa.
        DB::table('businesses')->select('id', 'nit')->orderBy('id')->chunk(200, function ($rows) {
            foreach ($rows as $row) {
                if (! preg_match('/^\s*([\d.]+)\s*-\s*(\d)\s*$/', (string) $row->nit, $matches)) {
                    continue;
                }

                DB::table('businesses')->where('id', $row->id)->update([
                    'verification_digit' => $matches[2],
                ]);
            }
        });

        // Un cliente con NIT es persona jurídica; el resto, persona natural.
        DB::table('clients')->where('document_type', 'NIT')->update(['person_type' => 1]);
    }

    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropColumn(['verification_digit', 'person_type', 'fiscal_responsibilities', 'postal_code']);
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['verification_digit', 'person_type', 'fiscal_responsibilities']);
        });
    }
};
