<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_type_id')->constrained()->cascadeOnDelete();
            $table->string('name')->unique();
            $table->string('label');
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['organization_type_id', 'label'], 'business_types_org_label_unique_idx');
            $table->index(['organization_type_id', 'deleted_at', 'active'], 'business_types_org_deleted_active_idx');
        });

        Schema::table('businesses', function (Blueprint $table) {
            $table->foreignId('business_type_id')
                ->nullable()
                ->after('organization_type_id')
                ->constrained('business_types')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropForeign(['business_type_id']);
            $table->dropColumn('business_type_id');
        });

        Schema::dropIfExists('business_types');
    }
};
