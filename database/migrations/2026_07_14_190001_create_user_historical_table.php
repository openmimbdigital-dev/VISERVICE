<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_historical', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->string('client_name')->nullable()->comment('Nombre del cliente al momento del evento');
            $table->string('action', 50)->comment('login, created, updated, deleted, status_changed, etc.');
            $table->string('module', 80)->nullable()->comment('auth, workshop.quotations, catalog.products, etc.');
            $table->string('description')->nullable();
            $table->nullableMorphs('subject');
            $table->string('subject_label')->nullable()->comment('Etiqueta legible del registro afectado');
            $table->json('properties')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['business_id', 'created_at'], 'user_historical_business_created_idx');
            $table->index(['business_id', 'user_id', 'created_at'], 'user_historical_business_user_created_idx');
            $table->index(['business_id', 'module', 'created_at'], 'user_historical_business_module_created_idx');
            $table->index(['business_id', 'client_id', 'created_at'], 'user_historical_business_client_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_historical');
    }
};
