<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipment_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('label');
            $table->boolean('active')->default(true);
            $table->boolean('general')->default(false)->comment('Si es true, el registro aplica para todos los negocios');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['business_id', 'name']);
            $table->index(['business_id', 'label']);
            $table->index(['business_id', 'deleted_at', 'created_at'], 'equipment_types_business_deleted_created_idx');
            $table->index(['general', 'deleted_at', 'active'], 'equipment_types_general_deleted_active_idx');
            $table->index(['deleted_at', 'created_at'], 'equipment_types_deleted_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment_types');
    }
};
