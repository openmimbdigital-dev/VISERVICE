<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menu_sections', function (Blueprint $table) {
            $table->boolean('assignable_to_business')->default(false)->after('active');
        });

        Schema::create('business_menu_section', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignId('menu_section_id')->constrained('menu_sections')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['business_id', 'menu_section_id'], 'business_menu_section_unique_idx');
            $table->index(['business_id'], 'business_menu_section_business_idx');
        });

        Schema::create('business_menu_item', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignId('menu_item_id')->constrained('menu_items')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['business_id', 'menu_item_id'], 'business_menu_item_unique_idx');
            $table->index(['business_id'], 'business_menu_item_business_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_menu_item');
        Schema::dropIfExists('business_menu_section');

        Schema::table('menu_sections', function (Blueprint $table) {
            $table->dropColumn('assignable_to_business');
        });
    }
};
