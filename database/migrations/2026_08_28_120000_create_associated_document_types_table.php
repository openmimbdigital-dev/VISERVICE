<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('associated_document_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('key', 150)->comment('Identificador único generado a partir del nombre');
            $table->string('name', 150)->comment('Nombre visible del documento asociado');
            $table->boolean('active')->default(true);
            $table->boolean('send_invoice')->default(false)->comment('Indica si el documento se envía a facturación (DIAN)');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['business_id', 'deleted_at', 'key'], 'associated_document_types_business_deleted_key_uidx');
            $table->index(['business_id', 'deleted_at', 'active'], 'associated_document_types_business_deleted_active_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('associated_document_types');
    }
};
