<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('remissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('work_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->foreignId('equipment_id')->nullable()->constrained('equipment')->nullOnDelete();
            $table->string('reference')->comment('REM-YYYYMM-XXXX');
            $table->enum('type', ['entrega', 'devolucion', 'traslado'])->default('entrega');
            $table->enum('status', ['borrador', 'emitida', 'entregada'])->default('borrador');
            $table->string('quotation_or_po_reference')->nullable()->comment('Cotización / Orden de compra');
            $table->date('issue_date')->nullable()->comment('Fecha de expedición');

            // Destino / entrega
            $table->string('delivery_address')->nullable();
            $table->string('delivery_city')->nullable();
            $table->string('delivery_contact')->nullable();
            $table->string('delivery_phone', 50)->nullable();
            $table->text('delivery_observations')->nullable();

            // Observaciones generales del documento
            $table->text('observations')->nullable();

            // Entregado por
            $table->string('delivered_by_name')->nullable();
            $table->string('delivered_by_position')->nullable();
            $table->string('delivered_by_document', 50)->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->string('delivered_by_signature')->nullable();

            // Recibido por
            $table->string('received_by_name')->nullable();
            $table->string('received_by_position')->nullable();
            $table->string('received_by_document', 50)->nullable();
            $table->timestamp('received_at')->nullable();
            $table->string('received_by_signature')->nullable();

            $table->unsignedInteger('total_items')->default(0);
            $table->timestamp('issued_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['business_id', 'reference']);
            $table->index(['business_id', 'deleted_at', 'created_at'], 'remissions_business_deleted_created_idx');
            $table->index(['business_id', 'deleted_at', 'status'], 'remissions_business_deleted_status_idx');
            $table->index(['business_id', 'deleted_at', 'type'], 'remissions_business_deleted_type_idx');
            $table->index(['work_order_id', 'deleted_at', 'status'], 'remissions_work_order_deleted_status_idx');
            $table->index(['client_id', 'deleted_at'], 'remissions_client_deleted_idx');
            $table->index(['equipment_id', 'deleted_at'], 'remissions_equipment_deleted_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('remissions');
    }
};
