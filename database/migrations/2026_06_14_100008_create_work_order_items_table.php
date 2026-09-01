<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained()->onDelete('cascade');
            $table->foreignId('equipment_id')->nullable()->constrained('equipment')->nullOnDelete();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedBigInteger('product_type_id')->nullable();
            $table->string('description');
            $table->decimal('quantity', 10, 2)->default(1);
            $table->decimal('quantity_complete', 10, 2)->default(0);
            $table->decimal('quantity_canceled', 10, 2)->default(0);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('discount_percentage', 5, 2)->default(0);
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->text('technician_notes')->nullable();
            $table->timestamps();

            $table->index(['work_order_id', 'product_type_id'], 'work_order_items_wo_type_idx');
            $table->index(['work_order_id', 'equipment_id'], 'work_order_items_wo_equipment_idx');
            $table->index(['equipment_id'], 'work_order_items_equipment_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_order_items');
    }
};
