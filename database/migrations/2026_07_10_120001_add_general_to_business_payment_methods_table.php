<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('business_payment_methods', function (Blueprint $table) {
            $table->boolean('general')->default(false)->after('label')
                ->comment('Si es true, el método aplica para todos los negocios');

            $table->foreignId('business_id')->nullable()->change();

            $table->index(['general', 'deleted_at', 'active'], 'business_payment_methods_general_deleted_active_idx');
        });
    }

    public function down(): void
    {
        Schema::table('business_payment_methods', function (Blueprint $table) {
            $table->dropIndex('business_payment_methods_general_deleted_active_idx');
            $table->dropColumn('general');
        });
    }
};
