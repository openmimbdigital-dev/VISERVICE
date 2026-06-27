<?php

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
            $table->enum('status', ['abierta', 'en_proceso', 'finalizada', 'cancelada'])->default('abierta');
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
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('finalized_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['business_id', 'reference']);
            $table->index(['business_id', 'status']);
            $table->index(['client_id', 'status']);
            $table->index(['equipment_id', 'status']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_orders');
    }
};
