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
            $table->enum('item_type', ['servicio', 'repuesto', 'otro'])->default('servicio');
            $table->string('description');
            $table->decimal('quantity', 10, 2)->default(1);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('discount_percentage', 5, 2)->default(0);
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->enum('status', ['pendiente', 'en_proceso', 'completado', 'cancelado'])->default('pendiente');
            $table->text('technician_notes')->nullable();
            $table->unsignedBigInteger('catalog_item_id')->nullable();
            $table->string('catalog_item_type')->nullable();
            $table->timestamps();

            $table->index(['work_order_id', 'item_type']);
            $table->index(['work_order_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_order_items');
    }
};
