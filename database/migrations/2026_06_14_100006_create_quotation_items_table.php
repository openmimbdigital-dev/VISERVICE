<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->constrained()->onDelete('cascade');
            $table->enum('item_type', ['servicio', 'repuesto', 'otro'])->default('servicio');
            $table->string('description');
            $table->decimal('quantity', 10, 2)->default(1);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('discount_percentage', 5, 2)->default(0);
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->unsignedBigInteger('catalog_item_id')->nullable()->comment('ID del item del catálogo si aplica');
            $table->string('catalog_item_type')->nullable()->comment('services_catalog | spare_parts_catalog');
            $table->timestamps();

            $table->index(['quotation_id', 'item_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotation_items');
    }
};
