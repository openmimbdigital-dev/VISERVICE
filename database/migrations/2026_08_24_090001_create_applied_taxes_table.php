<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applied_taxes', function (Blueprint $table) {
            $table->id();
            $table->string('taxable_type');
            $table->unsignedBigInteger('taxable_id');
            $table->foreignId('custom_tax_id')->nullable()->constrained('custom_taxes')->nullOnDelete();
            $table->string('custom_tax_name')->comment('Nombre del impuesto al momento de aplicarlo');
            $table->decimal('tax_percentage', 5, 2)->default(0)->comment('Porcentaje vigente al momento de aplicarlo');
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->timestamps();

            $table->index(['taxable_type', 'taxable_id'], 'applied_taxes_taxable_idx');
            $table->unique(['taxable_type', 'taxable_id', 'custom_tax_id'], 'applied_taxes_taxable_tax_uidx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applied_taxes');
    }
};
