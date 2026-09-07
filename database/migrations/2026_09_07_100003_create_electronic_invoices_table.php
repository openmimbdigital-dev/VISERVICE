<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('electronic_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('work_order_invoice_id')->unique()
                ->constrained('work_order_invoices')->cascadeOnDelete();

            $table->string('document_type', 20)->default('invoice')
                ->comment('invoice, credit_note, debit_note');
            $table->string('environment', 20)->default('test');

            // Numeración fiscal reservada dentro del rango de la resolución.
            $table->string('prefix', 10)->nullable();
            $table->unsignedBigInteger('consecutive');
            $table->string('document_number', 30)
                ->comment('Prefijo + consecutivo, ej. SETT945064');

            $table->string('status', 20)->default('pending')
                ->comment('pending, sent, accepted, rejected, error');

            // Respuesta del proveedor.
            $table->unsignedBigInteger('transaction_id')->nullable()
                ->comment('tr_id devuelto por el proveedor');
            $table->string('cufe', 200)->nullable();
            $table->text('qr_code')->nullable();
            $table->text('dian_status')->nullable()
                ->comment('Cronología de estados reportada por el proveedor');
            $table->integer('error_id')->nullable();
            $table->text('error_message')->nullable();

            // Trazabilidad: permite validar el formato con el proveedor ante un rechazo.
            $table->longText('request_xml')->nullable();
            $table->json('response_payload')->nullable();
            $table->unsignedInteger('attempts')->default(0);

            $table->dateTime('issued_at')->nullable();
            $table->dateTime('sent_at')->nullable();
            $table->dateTime('accepted_at')->nullable();
            $table->dateTime('status_checked_at')->nullable();

            $table->string('xml_path')->nullable();
            $table->string('pdf_path')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['business_id', 'document_number'], 'electronic_invoices_business_number_uidx');
            $table->index(['business_id', 'status'], 'electronic_invoices_business_status_idx');
            $table->index('transaction_id', 'electronic_invoices_transaction_idx');
            $table->index('cufe', 'electronic_invoices_cufe_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('electronic_invoices');
    }
};
