<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organization_type_role', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_type_id')->constrained()->cascadeOnDelete();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['organization_type_id', 'role_id'], 'organization_type_role_unique_idx');
        });

        Schema::create('organization_type_permission', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_type_id')->constrained()->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['organization_type_id', 'permission_id'], 'organization_type_permission_unique_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organization_type_permission');
        Schema::dropIfExists('organization_type_role');
    }
};
