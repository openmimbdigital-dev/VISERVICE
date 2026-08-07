<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_order_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->onDelete('cascade');
            $table->foreignId('work_order_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 12, 2);
            $table->decimal('percentage', 5, 2)->nullable()->comment('Snapshot % sobre el total de la OT al registrar el abono');
            $table->string('payment_method', 100)->nullable();
            $table->foreignId('business_payment_method_id')->nullable()->constrained('business_payment_methods')->nullOnDelete();
            $table->foreignId('business_bank_account_id')->nullable()->constrained('business_bank_accounts')->nullOnDelete();
            $table->string('payment_reference')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->string('status', 100)->default('confirmed');
            $table->foreign('status')->references('name')->on('statuses')->restrictOnDelete();
            $table->timestamp('cancelled_at')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('cancellation_reason')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['business_id', 'deleted_at', 'paid_at'], 'work_order_payments_business_deleted_paid_idx');
            $table->index(['business_id', 'deleted_at', 'status'], 'work_order_payments_business_deleted_status_idx');
            $table->index(['work_order_id', 'deleted_at', 'status'], 'work_order_payments_wo_deleted_status_idx');
            $table->index(['work_order_id', 'deleted_at', 'paid_at'], 'work_order_payments_wo_deleted_paid_idx');
            $table->index(
                ['business_id', 'business_payment_method_id', 'deleted_at', 'paid_at'],
                'work_order_payments_business_method_deleted_paid_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_order_payments');
    }
};
