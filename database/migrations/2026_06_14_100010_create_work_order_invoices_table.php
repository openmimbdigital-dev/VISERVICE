<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_order_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->onDelete('cascade');
            $table->foreignId('work_order_id')->constrained()->onDelete('cascade');
            $table->string('reference')->comment('FAC-YYYYMM-XXXX');
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('tax_percentage', 5, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->enum('status', ['pendiente', 'pagada', 'vencida', 'anulada'])->default('pendiente');
            $table->date('due_date')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('payment_reference')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['business_id', 'reference']);
            $table->index(['business_id', 'deleted_at', 'created_at'], 'work_order_invoices_business_deleted_created_idx');
            $table->index(['business_id', 'deleted_at', 'status'], 'work_order_invoices_business_deleted_status_idx');
            $table->index(['work_order_id', 'deleted_at', 'status'], 'work_order_invoices_work_order_deleted_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_order_invoices');
    }
};
