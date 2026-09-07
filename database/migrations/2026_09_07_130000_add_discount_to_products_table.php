<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->enum('discount_type', ['percentage', 'amount'])->nullable()->after('sale_price')
                ->comment('Cómo se expresa el descuento: porcentaje sobre el precio o valor fijo');
            $table->decimal('discount_value', 14, 2)->nullable()->after('discount_type')
                ->comment('Porcentaje (0-100) o valor por unidad, según discount_type');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['discount_type', 'discount_value']);
        });
    }
};
