<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organization_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_type_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('label');
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['business_type_id', 'label'], 'organization_types_type_label_unique_idx');
            $table->index(['business_type_id', 'deleted_at', 'active'], 'organization_types_type_deleted_active_idx');
        });

        Schema::table('businesses', function (Blueprint $table) {
            $table->foreignId('organization_type_id')
                ->nullable()
                ->after('business_type_id')
                ->constrained('organization_types')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropForeign(['organization_type_id']);
            $table->dropColumn('organization_type_id');
        });

        Schema::dropIfExists('organization_types');
    }
};
