<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->onDelete('cascade');
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->foreignId('brand_id')->nullable()->constrained('brands')->nullOnDelete();
            $table->foreignId('model_id')->nullable()->constrained('equipment_models')->nullOnDelete();
            $table->foreignId('equipment_type_id')->nullable()->constrained('equipment_types')->nullOnDelete();
            $table->string('plate')->comment('Número para identificar un equipo');
            $table->string('brand_name')->nullable()->comment('Marca');
            $table->string('client_name')->nullable()->comment('Cliente');
            $table->string('model_name')->nullable()->comment('Modelo');
            $table->string('equipment_type_name')->nullable()->comment('Modelo');
            $table->unsignedSmallInteger('year')->nullable();
            $table->unsignedInteger('km_current')->default(0);
            $table->boolean('status')->default(true);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['business_id', 'deleted_at', 'plate'], 'equipment_business_deleted_plate_idx');
            $table->index(['business_id', 'deleted_at', 'client_id'], 'equipment_business_deleted_client_idx');
            $table->index(['business_id', 'deleted_at', 'status'], 'equipment_business_deleted_status_idx');
            $table->index(['client_id', 'deleted_at', 'status'], 'equipment_client_deleted_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment');
    }
};
