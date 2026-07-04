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
            $table->foreignId('business_id')->constrained()->onDelete('cascade');
            $table->foreignId('work_order_id')->constrained()->onDelete('cascade');
            $table->string('reference')->comment('REM-YYYYMM-XXXX');
            $table->enum('status', ['borrador', 'emitida', 'entregada'])->default('borrador');
            $table->text('notes')->nullable();
            $table->json('items')->nullable()->comment('Items entregados en la remisión');
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['business_id', 'reference']);
            $table->index(['business_id', 'deleted_at', 'created_at'], 'remissions_business_deleted_created_idx');
            $table->index(['business_id', 'deleted_at', 'status'], 'remissions_business_deleted_status_idx');
            $table->index(['work_order_id', 'deleted_at', 'status'], 'remissions_work_order_deleted_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('remissions');
    }
};
