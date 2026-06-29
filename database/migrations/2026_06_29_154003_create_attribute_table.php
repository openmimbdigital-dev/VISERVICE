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

            // Índices únicos para evitar duplicados en la misma posición
            $table->unique(['type', 'name','deleted_at'], 'unique_attributes_name');

        });


        Schema::create('attribute_product_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('businesses');
            $table->morphs('model'); // model_id, model_type
            $table->foreignId('attribute_id')->nullable()->constrained('attributes');
            $table->boolean('general')->default(true);

            $table->softDeletes();
            $table->timestamps();
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
