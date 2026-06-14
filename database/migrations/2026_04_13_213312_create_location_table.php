<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('code', 3)->unique(); // Código ISO del país (ej: COL, MEX, ARG)
            $table->string('phone_code', 5)->nullable(); // Código telefónico (ej: +57, +52)
            $table->string('currency', 3)->nullable(); // Código de moneda (ej: COP, USD, EUR)
            $table->string('currency_symbol', 5)->nullable(); // Símbolo de moneda (ej: $, €, £)
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });


        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('code', 10)->nullable(); // Código de la ciudad (ej: BOG, MED, CAL)
            $table->unsignedBigInteger('country_id');
            $table->string('state_province', 100)->nullable(); // Estado o provincia
            $table->decimal('latitude', 10, 8)->nullable(); // Latitud para mapas
            $table->decimal('longitude', 11, 8)->nullable(); // Longitud para mapas
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            // Clave foránea
            $table->foreign('country_id')->references('id')->on('countries')->onDelete('cascade');

            // Índices
            $table->index('country_id');
            $table->index('name');
            $table->unique(['name', 'country_id']); // Una ciudad no puede repetirse en el mismo país
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('countries');
        Schema::dropIfExists('cities');
    }
};
