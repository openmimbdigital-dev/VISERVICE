<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->onDelete('cascade');
            $table->foreignId('city_id')->nullable()->constrained('cities')->nullOnDelete();
            $table->string('name');
            $table->enum('document_type', ['CC', 'NIT', 'CE', 'PA', 'PPT', 'TI'])->default('CC');
            $table->string('document_number')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->string('contact_name')->nullable()->comment('Nombre del contacto si es empresa');
            $table->boolean('status')->default(true);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['business_id', 'deleted_at', 'name'], 'clients_business_deleted_name_idx');
            $table->index(['business_id', 'deleted_at', 'created_at'], 'clients_business_deleted_created_idx');
            $table->index(['business_id', 'deleted_at', 'status'], 'clients_business_deleted_status_idx');
            $table->index(['business_id', 'document_number', 'deleted_at'], 'clients_business_document_deleted_idx');
            $table->index(['city_id', 'deleted_at'], 'clients_city_deleted_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
