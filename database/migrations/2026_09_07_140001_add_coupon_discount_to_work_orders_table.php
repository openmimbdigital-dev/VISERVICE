<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('work_orders', 'coupon_id')) {
            Schema::table('work_orders', function (Blueprint $table) {
                $table->foreignId('coupon_id')->nullable()->after('quotation_id')
                    ->constrained()->nullOnDelete();
                // Se guarda el código y el valor aplicados: si el cupón cambia o se
                // elimina después, la OT conserva el descuento con el que se acordó.
                $table->string('coupon_code', 40)->nullable()->after('coupon_id');
                $table->decimal('discount_amount', 14, 2)->default(0)->after('subtotal')
                    ->comment('Descuento del cupón sobre el subtotal de la OT');
            });
        }

        if (! Schema::hasColumn('quotations', 'coupon_id')) {
            Schema::table('quotations', function (Blueprint $table) {
                $table->foreignId('coupon_id')->nullable()->after('execution_time')
                    ->constrained()->nullOnDelete();
                $table->string('coupon_code', 40)->nullable()->after('coupon_id');
                $table->decimal('discount_amount', 14, 2)->default(0)->after('subtotal')
                    ->comment('Descuento del cupón sobre el subtotal de la cotización');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('work_orders', 'coupon_id')) {
            Schema::table('work_orders', function (Blueprint $table) {
                $table->dropForeign(['coupon_id']);
                $table->dropColumn(['coupon_id', 'coupon_code', 'discount_amount']);
            });
        }

        if (Schema::hasColumn('quotations', 'coupon_id')) {
            Schema::table('quotations', function (Blueprint $table) {
                $table->dropForeign(['coupon_id']);
                $table->dropColumn(['coupon_id', 'coupon_code', 'discount_amount']);
            });
        }
    }
};
