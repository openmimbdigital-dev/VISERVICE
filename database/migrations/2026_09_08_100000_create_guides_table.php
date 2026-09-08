<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guides', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique()->comment('Identificador en la URL');
            $table->string('module', 80)->comment('Módulo del sistema al que pertenece, para agrupar el listado');
            $table->enum('type', ['onboarding', 'documentation'])->default('documentation');
            $table->string('summary', 300)->nullable()->comment('Una línea, para el listado');
            $table->longText('content')->comment('Contenido en Markdown');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('published')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['module', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guides');
    }
};
