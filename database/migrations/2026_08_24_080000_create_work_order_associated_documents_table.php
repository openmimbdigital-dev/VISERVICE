<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('work_orders', 'document_client')) {
            Schema::table('work_orders', function (Blueprint $table) {
                $table->dropColumn('document_client');
            });
        }

        Schema::create('work_order_associated_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained()->cascadeOnDelete();
            $table->string('name')->comment('Nombre del documento asociado');
            $table->string('value')->comment('Valor o referencia del documento');
            $table->timestamps();

            $table->unique(['work_order_id', 'name'], 'work_order_associated_documents_wo_name_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_order_associated_documents');

        if (Schema::hasTable('work_orders') && ! Schema::hasColumn('work_orders', 'document_client')) {
            Schema::table('work_orders', function (Blueprint $table) {
                $table->json('document_client')->nullable()->comment('Documentos asociados: {label: valor}');
            });
        }
    }
};
