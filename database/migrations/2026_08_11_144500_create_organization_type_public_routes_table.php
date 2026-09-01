<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organization_type_public_routes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_type_id')->constrained()->cascadeOnDelete();
            $table->string('route_key', 100);
            $table->timestamps();

            $table->unique(
                ['organization_type_id', 'route_key'],
                'org_type_public_routes_type_key_unique'
            );
            $table->index(
                ['route_key', 'organization_type_id'],
                'org_type_public_routes_key_type_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organization_type_public_routes');
    }
};
