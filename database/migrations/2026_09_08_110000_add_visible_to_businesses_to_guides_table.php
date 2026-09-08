<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('guides', function (Blueprint $table) {
            // Por defecto una guía es interna: se comparte con los negocios solo
            // cuando alguien lo decide explícitamente.
            $table->boolean('visible_to_businesses')->default(false)->after('published')
                ->comment('Si los usuarios de los negocios pueden leerla');
        });
    }

    public function down(): void
    {
        Schema::table('guides', function (Blueprint $table) {
            $table->dropColumn('visible_to_businesses');
        });
    }
};
