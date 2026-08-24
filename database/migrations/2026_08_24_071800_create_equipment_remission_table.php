<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipment_remission', function (Blueprint $table) {
            $table->id();
            $table->foreignId('remission_id')->constrained('remissions')->cascadeOnDelete();
            $table->foreignId('equipment_id')->constrained('equipment')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(
                ['remission_id', 'equipment_id'],
                'equipment_remission_unique'
            );
            $table->index(
                ['equipment_id', 'remission_id'],
                'equipment_remission_equipment_remission_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment_remission');
    }
};
