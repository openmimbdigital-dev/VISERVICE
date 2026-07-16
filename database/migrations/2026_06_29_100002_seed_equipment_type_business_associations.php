<?php

use Database\Seeders\Support\EquipmentTypeBusinessSeeder;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        EquipmentTypeBusinessSeeder::run();
    }

    public function down(): void
    {
        // No revertir asociaciones: los tipos siguen existiendo sin pivote (compatibilidad legacy).
    }
};
