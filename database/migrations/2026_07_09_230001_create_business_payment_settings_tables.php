<?php

use App\Enums\BusinessBankAccountType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_payment_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name', 100);
            $table->string('label', 100);
            $table->boolean('general')->default(false)->comment('Si es true, el método aplica para todos los negocios');
            $table->boolean('active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['business_id', 'deleted_at', 'active'], 'business_payment_methods_business_deleted_active_idx');
            $table->index(['business_id', 'deleted_at', 'sort_order'], 'business_payment_methods_business_deleted_sort_idx');
            $table->index(['general', 'deleted_at', 'active'], 'business_payment_methods_general_deleted_active_idx');
        });

        Schema::create('business_bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bank_id')->nullable()->constrained('banks')->nullOnDelete();
            $table->string('bank_name', 120);
            $table->enum('account_type', array_column(BusinessBankAccountType::cases(), 'value'))
                ->default(BusinessBankAccountType::Corriente->value);
            $table->string('account_number', 50);
            $table->string('account_holder', 150);
            $table->string('document_type', 20)->default('NIT');
            $table->string('document_number', 30);
            $table->boolean('is_default')->default(false);
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['business_id', 'deleted_at', 'active'], 'business_bank_accounts_business_deleted_active_idx');
            $table->index(['business_id', 'deleted_at', 'is_default'], 'business_bank_accounts_business_deleted_default_idx');
        });

        if (Schema::hasTable('quotations')) {
            Schema::table('quotations', function (Blueprint $table) {
                $table->foreign('business_payment_method_id')
                    ->references('id')->on('business_payment_methods')->nullOnDelete();
                $table->foreign('business_bank_account_id')
                    ->references('id')->on('business_bank_accounts')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('quotations')) {
            Schema::table('quotations', function (Blueprint $table) {
                $table->dropForeign(['business_payment_method_id']);
                $table->dropForeign(['business_bank_account_id']);
            });
        }

        Schema::dropIfExists('business_bank_accounts');
        Schema::dropIfExists('business_payment_methods');
    }
};
