<?php

use App\Enums\WorkOrderStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->onDelete('cascade');
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->foreignId('equipment_id')->constrained('equipment')->onDelete('cascade');
            $table->foreignId('quotation_id')->nullable()->constrained('quotations')->nullOnDelete();
            $table->string('reference')->comment('OT-YYYYMM-XXXX');
            $table->enum('status', array_column(WorkOrderStatus::cases(), 'value'))->default(WorkOrderStatus::Created->value);
            $table->json('status_comments')->nullable()->comment('Historial de comentarios por cambio de estado');
            $table->unsignedInteger('km_entry')->default(0);
            $table->unsignedInteger('km_exit')->nullable();
            $table->text('diagnosis')->nullable()->comment('Diagnóstico registrado al ingreso');
            $table->text('work_description')->nullable()->comment('Descripción del trabajo realizado');
            $table->text('observations')->nullable();
            $table->text('notes')->nullable();
            $table->date('estimated_delivery')->nullable();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('tax_percentage', 5, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->decimal('advance_percentage', 5, 2)->default(0);
            $table->decimal('advance_amount', 12, 2)->default(0);
            $table->json('document_client')->nullable()->comment('Documentos asociados: {label: valor}');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('finalized_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['business_id', 'reference']);

            // Listado datatable y conteos por equipo/cliente
            $table->index(['business_id', 'deleted_at', 'created_at'], 'work_orders_business_deleted_created_idx');
            $table->index(['business_id', 'deleted_at', 'status'], 'work_orders_business_deleted_status_idx');
            $table->index(['business_id', 'deleted_at', 'reference'], 'work_orders_business_deleted_reference_idx');
            $table->index(['client_id', 'deleted_at', 'status'], 'work_orders_client_deleted_status_idx');
            $table->index(['equipment_id', 'deleted_at', 'status'], 'work_orders_equipment_deleted_status_idx');
            $table->index(['quotation_id', 'deleted_at'], 'work_orders_quotation_deleted_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_orders');
    }
};
