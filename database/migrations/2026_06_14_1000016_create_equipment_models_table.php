<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipment_models', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('brand_id')->constrained('brands')->cascadeOnDelete();
            $table->string('name');
            $table->string('label');
            $table->boolean('active')->default(true);
            $table->boolean('general')->default(false)->comment('Si es true, el registro aplica para todos los negocios');
            $table->timestamps();

            $table->unique(['business_id', 'brand_id', 'name']);
            $table->unique(['business_id', 'brand_id', 'label']);
            $table->index(['business_id', 'brand_id', 'active']);
            $table->index(['brand_id', 'active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment_models');
    }
};
