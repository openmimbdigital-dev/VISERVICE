<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('brand_equipment_type', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_id')->constrained()->cascadeOnDelete();
            $table->foreignId('equipment_type_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['brand_id', 'equipment_type_id'], 'brand_equipment_type_unique_idx');
            // Marcas asociadas a un tipo de equipo
            $table->index(['equipment_type_id', 'brand_id'], 'brand_equipment_type_type_brand_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('brand_equipment_type');
    }
};
