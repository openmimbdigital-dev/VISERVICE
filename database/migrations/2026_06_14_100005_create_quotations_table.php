<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotation_service_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name', 100);
            $table->string('label', 100);
            $table->boolean('active')->default(true);
            $table->boolean('general')->default(false)->comment('Si es true, aplica para todos los negocios');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['business_id', 'deleted_at', 'created_at'], 'quotation_service_types_business_deleted_created_idx');
            $table->index(['general', 'deleted_at', 'active'], 'quotation_service_types_general_deleted_active_idx');
        });

        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->onDelete('cascade');
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->foreignId('equipment_id')->constrained('equipment')->onDelete('cascade');
            $table->foreignId('quotation_service_type_id')->nullable()->constrained('quotation_service_types')->nullOnDelete();
            $table->unsignedBigInteger('business_payment_method_id')->nullable();
            $table->unsignedBigInteger('business_bank_account_id')->nullable();
            $table->string('reference')->comment('COT-YYYYMM-XXXX');
            $table->string('status', 100)->default('created');
            $table->foreign('status')->references('name')->on('statuses')->restrictOnDelete();
            $table->text('diagnosis')->nullable()->comment('Diagnóstico inicial del equipo');
            $table->time('hours_entry')->nullable()->comment('Horas de uso al ingreso (formato HH:MM)');
            $table->unsignedSmallInteger('validity_days')->default(15)->comment('Días de vigencia de la oferta');
            $table->date('valid_until')->nullable()->comment('Fecha de vencimiento de la cotización');
            $table->string('execution_time', 120)->nullable()->comment('Tiempo estimado de ejecución');
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('tax_percentage', 5, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->decimal('advance_percentage', 5, 2)->default(0);
            $table->decimal('advance_amount', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->text('observations')->nullable();
            $table->string('reject_reason', 500)->nullable();
            $table->string('approved_by_name', 150)->nullable();
            $table->string('approved_by_position', 120)->nullable();
            $table->string('approved_signature', 255)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['business_id', 'reference']);

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
        Schema::dropIfExists('quotation_service_types');
    }
};
