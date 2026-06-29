<?php

use App\Enums\AttributeType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('attributes', function (Blueprint $table) {
            $table->id();
            $table->enum('type', array_column(AttributeType::cases(), 'value'));
            $table->string('name');
            $table->boolean('required')->default(false);
            $table->integer('max_value')->nullable();
            $table->integer('min_value')->nullable();
            $table->boolean('default')->default(false);
            $table->boolean('nullable_creation')->default(false);
            $table->boolean('general')->default(false);
            $table->json('options')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['type', 'name', 'deleted_at'], 'unique_attributes_name');
            $table->index(['general', 'deleted_at'], 'attributes_general_deleted_idx');
            $table->index(['deleted_at', 'type'], 'attributes_deleted_type_idx');
            $table->index(['deleted_at', 'created_at'], 'attributes_deleted_created_idx');
        });

        Schema::create('attribute_business', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attribute_id')->constrained('attributes')->cascadeOnDelete();
            $table->foreignId('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['attribute_id', 'business_id'], 'attribute_business_unique');
            $table->index(['business_id', 'attribute_id'], 'attribute_business_business_idx');
        });

        Schema::create('attribute_equipment_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->nullable()->constrained('businesses')->nullOnDelete();
            $table->morphs('model');
            $table->foreignId('attribute_id')->nullable()->constrained('attributes');
            $table->boolean('general')->default(false);

            $table->softDeletes();
            $table->timestamps();

            $table->index(['business_id', 'deleted_at'], 'apt_business_deleted_idx');
            $table->index(['attribute_id', 'deleted_at'], 'apt_attribute_deleted_idx');
            $table->index(['model_type', 'model_id', 'deleted_at'], 'apt_model_deleted_idx');
        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attribute_equipment_types');
        Schema::dropIfExists('attribute_business');
        Schema::dropIfExists('attributes');
    }
};
