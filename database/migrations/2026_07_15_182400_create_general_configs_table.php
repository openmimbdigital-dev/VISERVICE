<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('general_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->morphs('configurable');
            $table->string('key', 100)->comment('Nombre de la variable general');
            $table->text('value')->nullable()->comment('Valor asignado a la variable');
            $table->timestamps();

            // Una sola key por entidad polimórfica dentro del negocio
            $table->unique(
                ['business_id', 'configurable_type', 'configurable_id', 'key'],
                'general_configs_business_configurable_key_uidx'
            );

            // Búsqueda de una key dentro del negocio (listados / lookups)
            $table->index(
                ['business_id', 'key'],
                'general_configs_business_key_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('general_configs');
    }
};
