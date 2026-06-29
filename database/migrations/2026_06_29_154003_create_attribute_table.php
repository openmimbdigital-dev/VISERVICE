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
            $table->foreignId('business_id')->constrained('businesses')->onDelete('cascade');
            $table->boolean('general')->default(true);
            $table->json('options')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['type', 'name', 'deleted_at'], 'unique_attributes_name');
            $table->index(['business_id', 'deleted_at', 'type'], 'attributes_business_deleted_type_idx');
            $table->index(['business_id', 'deleted_at', 'created_at'], 'attributes_business_deleted_created_idx');
            $table->index(['general', 'deleted_at'], 'attributes_general_deleted_idx');
        });


        Schema::create('attribute_product_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('businesses');
            $table->morphs('model'); // model_id, model_type
            $table->foreignId('attribute_id')->nullable()->constrained('attributes');
            $table->boolean('general')->default(true);

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
        Schema::dropIfExists('attribute_product_types');
        Schema::dropIfExists('attributes');
    }
};
