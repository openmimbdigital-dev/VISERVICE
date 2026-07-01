<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->foreignId('business_category_id')
                ->nullable()
                ->after('business_type_id')
                ->constrained('business_categories')
                ->nullOnDelete();

            $table->index(['business_category_id', 'deleted_at'], 'businesses_category_deleted_idx');
        });
    }

    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropForeign(['business_category_id']);
            $table->dropColumn('business_category_id');
        });
    }
};
