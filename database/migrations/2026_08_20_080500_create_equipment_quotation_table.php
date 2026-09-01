<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipment_quotation', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->constrained('quotations')->cascadeOnDelete();
            $table->foreignId('equipment_id')->constrained('equipment')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(
                ['quotation_id', 'equipment_id'],
                'equipment_quotation_unique'
            );
            $table->index(
                ['equipment_id', 'quotation_id'],
                'equipment_quotation_equipment_quotation_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment_quotation');
    }
};
