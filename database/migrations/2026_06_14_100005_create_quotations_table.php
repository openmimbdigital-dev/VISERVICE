<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->onDelete('cascade');
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->foreignId('equipment_id')->constrained('equipment')->onDelete('cascade');
            $table->string('reference')->comment('COT-YYYYMM-XXXX');
            $table->enum('status', ['borrador', 'enviada', 'aceptada', 'rechazada', 'vencida'])->default('borrador');
            $table->text('diagnosis')->nullable()->comment('Diagnóstico inicial del equipo');
            $table->unsignedInteger('km_entry')->default(0)->comment('Kilometraje al ingreso');
            $table->date('valid_until')->nullable()->comment('Fecha de vencimiento de la cotización');
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('tax_percentage', 5, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->text('observations')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['business_id', 'reference']);

            // Listado datatable: filtro negocio + soft delete + orden/fecha/estado
            $table->index(['business_id', 'deleted_at', 'created_at'], 'quotations_business_deleted_created_idx');
            $table->index(['business_id', 'deleted_at', 'status'], 'quotations_business_deleted_status_idx');
            $table->index(['business_id', 'deleted_at', 'reference'], 'quotations_business_deleted_reference_idx');
            $table->index(['client_id', 'deleted_at', 'status'], 'quotations_client_deleted_status_idx');
            $table->index(['equipment_id', 'deleted_at'], 'quotations_equipment_deleted_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotations');
    }
};
