<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dian_request_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->nullable()->constrained()->nullOnDelete();
            // La bitácora es traza de auditoría: sobrevive aunque se borre el documento.
            $table->foreignId('electronic_invoice_id')->nullable()
                ->constrained('electronic_invoices')->nullOnDelete();
            $table->foreignId('work_order_invoice_id')->nullable()
                ->constrained('work_order_invoices')->nullOnDelete();

            $table->string('operation', 40)
                ->comment('authenticate, emit, status, download, detail, register_company, ...');
            $table->string('endpoint');
            $table->string('environment', 20)->default('test');

            $table->boolean('success')->default(false);
            $table->unsignedSmallInteger('http_status')->nullable();
            $table->integer('error_id')->nullable();
            $table->text('error_message')->nullable();

            // El documento enviado se guarda ya legible; nunca se registran token ni contraseña.
            $table->longText('request_payload')->nullable();
            $table->longText('response_payload')->nullable();

            $table->unsignedInteger('attempt')->default(1);
            $table->unsignedInteger('duration_ms')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['electronic_invoice_id', 'created_at'], 'dian_logs_invoice_created_idx');
            $table->index(['work_order_invoice_id', 'created_at'], 'dian_logs_wo_invoice_created_idx');
            $table->index(['business_id', 'success', 'created_at'], 'dian_logs_business_success_idx');
            $table->index(['operation', 'created_at'], 'dian_logs_operation_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dian_request_logs');
    }
};
