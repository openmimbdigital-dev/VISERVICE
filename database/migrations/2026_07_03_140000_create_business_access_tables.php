<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_role', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['business_id', 'role_id'], 'business_role_unique_idx');
            $table->index(['business_id'], 'business_role_business_idx');
        });

        Schema::create('business_permission', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['business_id', 'permission_id'], 'business_permission_unique_idx');
            $table->index(['business_id'], 'business_permission_business_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_permission');
        Schema::dropIfExists('business_role');
    }
};
