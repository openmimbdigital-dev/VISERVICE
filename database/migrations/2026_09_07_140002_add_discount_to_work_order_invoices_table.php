<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_order_invoices', function (Blueprint $table) {
            // La factura guarda su propia copia: es un documento, y no debe cambiar
            // si después se toca el cupón o la OT.
            $table->decimal('discount_amount', 12, 2)->default(0)->after('subtotal');
            $table->string('coupon_code', 40)->nullable()->after('discount_amount');
        });
    }

    public function down(): void
    {
        Schema::table('work_order_invoices', function (Blueprint $table) {
            $table->dropColumn(['discount_amount', 'coupon_code']);
        });
    }
};
