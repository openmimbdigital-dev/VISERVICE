<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('brand_usage', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_id')->constrained('brands')->cascadeOnDelete();
            $table->string('type', 20);
            $table->timestamps();

            $table->unique(['brand_id', 'type'], 'brand_usage_brand_type_unique_idx');
            $table->index(['type', 'brand_id'], 'brand_usage_type_brand_idx');
        });

        $now = now();

        DB::table('brands')
            ->whereNull('deleted_at')
            ->orderBy('id')
            ->pluck('id')
            ->each(function (int $brand_id) use ($now) {
                DB::table('brand_usage')->insertOrIgnore([
                    'brand_id'   => $brand_id,
                    'type'       => 'equipment',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('brand_usage');
    }
};
