<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->onDelete('cascade');
            $table->foreignId('work_order_id')->constrained()->onDelete('cascade');
            $table->string('reference')->comment('OC-YYYYMM-XXXX');
            $table->string('supplier_name');
            $table->string('supplier_nit')->nullable();
            $table->string('supplier_phone')->nullable();
            $table->enum('status', ['borrador', 'enviada', 'recibida', 'cancelada'])->default('borrador');
            $table->date('expected_delivery')->nullable();
            $table->decimal('total', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['business_id', 'reference']);
            $table->index(['business_id', 'deleted_at', 'created_at'], 'purchase_orders_business_deleted_created_idx');
            $table->index(['business_id', 'deleted_at', 'status'], 'purchase_orders_business_deleted_status_idx');
            $table->index(['work_order_id', 'deleted_at', 'status'], 'purchase_orders_work_order_deleted_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
