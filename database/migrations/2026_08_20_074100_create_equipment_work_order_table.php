<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipment_work_order', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained('work_orders')->cascadeOnDelete();
            $table->foreignId('equipment_id')->constrained('equipment')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(
                ['work_order_id', 'equipment_id'],
                'equipment_work_order_unique'
            );
            $table->index(
                ['equipment_id', 'work_order_id'],
                'equipment_work_order_equipment_wo_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment_work_order');
    }
};
