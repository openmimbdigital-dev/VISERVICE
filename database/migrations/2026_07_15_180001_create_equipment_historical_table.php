<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipment_historical', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('equipment_id')->constrained('equipment')->cascadeOnDelete();
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->string('client_name')->nullable()->comment('Nombre del cliente al momento del evento');
            $table->string('equipment_plate')->nullable()->comment('Placa del equipo al momento del evento');
            $table->string('equipment_label')->nullable()->comment('Etiqueta legible del equipo al momento del evento');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 50)->comment('created, updated, deleted, status_changed, associated, etc.');
            $table->string('module', 80)->comment('workshop.quotations, workshop.work-orders, workshop.equipment, etc.');
            $table->string('description')->nullable();
            $table->nullableMorphs('subject');
            $table->string('subject_reference')->nullable()->comment('Referencia COT-/OT- u otra');
            $table->string('subject_status', 50)->nullable()->comment('Estado del documento al momento del evento');
            $table->json('items')->nullable()->comment('Productos y servicios asociados al evento');
            $table->decimal('subtotal', 12, 2)->nullable();
            $table->decimal('tax_percentage', 5, 2)->nullable();
            $table->decimal('tax_amount', 12, 2)->nullable();
            $table->decimal('total', 12, 2)->nullable();
            $table->json('properties')->nullable();
            $table->timestamp('created_at')->useCurrent();

            // Listados por equipo / cliente / módulo (acceso habitual por negocio)
            $table->index(['business_id', 'equipment_id', 'created_at'], 'equipment_historical_business_equipment_created_idx');
            $table->index(['business_id', 'client_id', 'created_at'], 'equipment_historical_business_client_created_idx');
            $table->index(['business_id', 'module', 'created_at'], 'equipment_historical_business_module_created_idx');
            $table->index(['business_id', 'action', 'created_at'], 'equipment_historical_business_action_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment_historical');
    }
};
