<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('item_types', function (Blueprint $table) {
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
            $table->index(['business_id', 'deleted_at', 'created_at'], 'item_types_business_deleted_created_idx');
            $table->index(['general', 'deleted_at', 'active'], 'item_types_general_deleted_active_idx');
            $table->index(['deleted_at', 'created_at'], 'item_types_deleted_created_idx');
        });

        Schema::create('item_categories', function (Blueprint $table) {
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
            $table->index(['business_id', 'deleted_at', 'created_at'], 'item_categories_business_deleted_created_idx');
            $table->index(['general', 'deleted_at', 'active'], 'item_categories_general_deleted_active_idx');
            $table->index(['deleted_at', 'created_at'], 'item_categories_deleted_created_idx');
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

        Schema::create('brand_item_category', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_id')->constrained('brands')->cascadeOnDelete();
            $table->foreignId('item_category_id')->constrained('item_categories')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['brand_id', 'item_category_id'], 'brand_item_category_unique_idx');
            $table->index(['item_category_id', 'brand_id'], 'brand_item_category_category_brand_idx');
        });

        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('item_type_id')->constrained('item_types');
            $table->foreignId('item_category_id')->constrained('item_categories');
            $table->foreignId('unit_id')->constrained('units');
            $table->foreignId('brand_id')->nullable()->constrained('brands')->nullOnDelete();
            $table->string('code', 50);
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('cost_price', 14, 2)->default(0);
            $table->decimal('sale_price', 14, 2)->default(0);
            $table->unsignedBigInteger('tax_id')->nullable();
            $table->boolean('track_inventory')->default(true);
            $table->boolean('status')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['business_id', 'code', 'deleted_at'], 'items_business_code_deleted_unique');
            $table->index(['business_id', 'deleted_at', 'name'], 'items_business_deleted_name_idx');
            $table->index(['business_id', 'deleted_at', 'created_at'], 'items_business_deleted_created_idx');
            $table->index(['business_id', 'deleted_at', 'status'], 'items_business_deleted_status_idx');
            $table->index(['business_id', 'item_type_id', 'deleted_at'], 'items_business_type_deleted_idx');
            $table->index(['business_id', 'item_category_id', 'deleted_at'], 'items_business_category_deleted_idx');
        });

        Schema::table('quotation_items', function (Blueprint $table) {
            $table->foreign('item_id')->references('id')->on('items')->nullOnDelete();
            $table->foreign('item_type_id')->references('id')->on('item_types')->nullOnDelete();
            $table->foreign('item_category_id')->references('id')->on('item_categories')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('quotation_items', function (Blueprint $table) {
            $table->dropForeign(['item_id']);
            $table->dropForeign(['item_type_id']);
            $table->dropForeign(['item_category_id']);
        });

        Schema::dropIfExists('items');
        Schema::dropIfExists('brand_item_category');
        Schema::dropIfExists('units');
        Schema::dropIfExists('item_categories');
        Schema::dropIfExists('item_types');
    }
};
