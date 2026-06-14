<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->onDelete('cascade');
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->string('plate')->comment('Número de placa');
            $table->string('brand')->nullable()->comment('Marca');
            $table->string('model')->nullable()->comment('Modelo');
            $table->unsignedSmallInteger('year')->nullable();
            $table->string('color')->nullable();
            $table->enum('fuel_type', ['gasolina', 'diesel', 'gas', 'electrico', 'hibrido', 'otro'])->default('gasolina');
            $table->string('engine_cc')->nullable()->comment('Cilindraje');
            $table->string('vin')->nullable()->comment('Número VIN / serial');
            $table->unsignedInteger('km_current')->default(0);
            $table->boolean('status')->default(true);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['business_id', 'plate']);
            $table->index(['business_id', 'status']);
            $table->index(['client_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
