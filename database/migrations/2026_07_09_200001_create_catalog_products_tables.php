<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('label');
            $table->boolean('active')->default(true);
            $table->boolean('general')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['business_id', 'name']);
            $table->index(['business_id', 'label']);
            $table->index(['business_id', 'deleted_at', 'created_at'], 'product_types_business_deleted_created_idx');
            $table->index(['general', 'deleted_at', 'active'], 'product_types_general_deleted_active_idx');
            $table->index(['deleted_at', 'created_at'], 'product_types_deleted_created_idx');
        });

        Schema::create('product_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('label');
            $table->boolean('inventory')->default(false)->comment('Indica si la categoría es cuantificable');
            $table->boolean('active')->default(true);
            $table->boolean('general')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['business_id', 'name']);
            $table->index(['business_id', 'label']);
            $table->index(['business_id', 'deleted_at', 'created_at'], 'product_categories_business_deleted_created_idx');
            $table->index(['general', 'deleted_at', 'active'], 'product_categories_general_deleted_active_idx');
            $table->index(['deleted_at', 'created_at'], 'product_categories_deleted_created_idx');
        });

        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('label');
            $table->string('symbol', 20);
            $table->boolean('active')->default(true);
            $table->boolean('general')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['business_id', 'name']);
            $table->index(['business_id', 'label']);
            $table->index(['business_id', 'deleted_at', 'created_at'], 'units_business_deleted_created_idx');
            $table->index(['general', 'deleted_at', 'active'], 'units_general_deleted_active_idx');
            $table->index(['deleted_at', 'created_at'], 'units_deleted_created_idx');
        });

        Schema::create('brand_product_category', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_id')->constrained('brands')->cascadeOnDelete();
            $table->foreignId('product_category_id')->constrained('product_categories')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['brand_id', 'product_category_id'], 'brand_product_category_unique_idx');
            $table->index(['product_category_id', 'brand_id'], 'brand_product_category_category_brand_idx');
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('step')->default(1)->comment('Paso actual del flujo de alta');
            $table->unsignedTinyInteger('final_step')->default(3)->comment('Pasos totales del flujo de alta');
            $table->foreignId('product_type_id')->nullable()->constrained('product_types');
            $table->foreignId('product_category_id')->nullable()->constrained('product_categories');
            $table->foreignId('unit_id')->nullable()->constrained('units');
            $table->foreignId('brand_id')->nullable()->constrained('brands')->nullOnDelete();
            $table->string('sku', 50);
            $table->string('barcode', 64)->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('cost_price', 14, 2)->nullable();
            $table->decimal('profit_percentage', 8, 2)->nullable()->comment('Porcentaje de ganancia sobre el costo');
            $table->decimal('sale_price', 14, 2)->nullable();
            $table->boolean('track_inventory')->default(true);
            $table->boolean('status')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['business_id', 'sku', 'deleted_at'], 'products_business_sku_deleted_unique');
            $table->unique(['business_id', 'barcode', 'deleted_at'], 'products_business_barcode_deleted_unique');
            $table->index(['business_id', 'deleted_at', 'name'], 'products_business_deleted_name_idx');
            $table->index(['business_id', 'deleted_at', 'created_at'], 'products_business_deleted_created_idx');
            $table->index(['business_id', 'deleted_at', 'status'], 'products_business_deleted_status_idx');
            $table->index(['business_id', 'deleted_at', 'step'], 'products_business_deleted_step_idx');
            $table->index(['business_id', 'product_type_id', 'deleted_at'], 'products_business_type_deleted_idx');
            $table->index(['business_id', 'product_category_id', 'deleted_at'], 'products_business_category_deleted_idx');
        });

        Schema::table('quotation_items', function (Blueprint $table) {
            $table->foreign('product_id')->references('id')->on('products')->nullOnDelete();
            $table->foreign('product_type_id')->references('id')->on('product_types')->nullOnDelete();
            $table->foreign('product_category_id')->references('id')->on('product_categories')->nullOnDelete();
        });

        Schema::table('work_order_items', function (Blueprint $table) {
            $table->foreign('product_id')->references('id')->on('products')->nullOnDelete();
            $table->foreign('product_type_id')->references('id')->on('product_types')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('work_order_items', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropForeign(['product_type_id']);
        });

        Schema::table('quotation_items', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropForeign(['product_type_id']);
            $table->dropForeign(['product_category_id']);
        });

        Schema::dropIfExists('products');
        Schema::dropIfExists('brand_product_category');
        Schema::dropIfExists('units');
        Schema::dropIfExists('product_categories');
        Schema::dropIfExists('product_types');
    }
};
