<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('remission_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('remission_id')->constrained('remissions')->cascadeOnDelete();
            $table->foreignId('work_order_item_id')->nullable()->constrained('work_order_items')->nullOnDelete();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedBigInteger('product_type_id')->nullable();
            $table->unsignedBigInteger('product_category_id')->nullable();
            $table->unsignedBigInteger('unit_id')->nullable();
            $table->string('description');
            $table->string('reference_brand')->nullable()->comment('Referencia / marca del producto');
            $table->string('unit_name')->nullable()->comment('Nombre de unidad al momento de emitir');
            $table->decimal('quantity', 10, 2)->default(1);
            $table->text('observations')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['remission_id', 'sort_order'], 'remission_items_remission_sort_idx');
            $table->index(['remission_id', 'product_type_id'], 'remission_items_remission_type_idx');
            $table->index(['remission_id', 'product_category_id'], 'remission_items_remission_category_idx');
            $table->index(['work_order_item_id'], 'remission_items_work_order_item_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('remission_items');
    }
};
