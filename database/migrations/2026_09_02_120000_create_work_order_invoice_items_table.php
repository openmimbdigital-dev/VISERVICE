<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_order_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_invoice_id')->constrained('work_order_invoices')->cascadeOnDelete();
            $table->foreignId('work_order_item_id')->constrained('work_order_items')->restrictOnDelete();
            $table->decimal('quantity', 10, 2)->default(1);
            $table->decimal('quantity_complete', 10, 2)->default(0);
            $table->decimal('quantity_canceled', 10, 2)->default(0);
            $table->timestamps();

            $table->unique(
                ['work_order_invoice_id', 'work_order_item_id'],
                'wo_invoice_items_invoice_item_uidx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_order_invoice_items');
    }
};
