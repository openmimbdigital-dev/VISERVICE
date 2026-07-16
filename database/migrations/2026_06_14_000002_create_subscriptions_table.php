<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignId('subscription_plan_id')->constrained('subscription_plans');
            $table->enum('status', ['trial', 'active', 'past_due', 'cancelled', 'expired'])->default('active');
            $table->enum('billing_cycle', ['monthly', 'quarterly', 'semiannual', 'annual'])->default('monthly');
            $table->decimal('monthly_price', 10, 2);     // precio mensual al momento de suscribir
            $table->decimal('total_price', 10, 2);       // total del ciclo de facturación
            $table->decimal('discount_percentage', 5, 2)->default(0);
            $table->date('started_at');
            $table->date('ends_at');
            $table->date('trial_ends_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->boolean('auto_renew')->default(true);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['business_id', 'deleted_at', 'status'], 'subscriptions_business_deleted_status_idx');
            $table->index(['business_id', 'deleted_at', 'ends_at'], 'subscriptions_business_deleted_ends_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
