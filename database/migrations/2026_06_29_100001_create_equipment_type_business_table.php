<?php

use Database\Seeders\Support\EquipmentTypeBusinessSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipment_type_business', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipment_type_id')->constrained()->cascadeOnDelete();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['equipment_type_id', 'business_id'], 'equipment_type_business_unique_idx');
            // Listado de tipos visibles por negocio
            $table->index(['business_id', 'equipment_type_id'], 'equipment_type_business_business_type_idx');
        });

        EquipmentTypeBusinessSeeder::run();
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment_type_business');
    }
};
