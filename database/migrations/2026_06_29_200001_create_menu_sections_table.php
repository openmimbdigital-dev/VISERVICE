<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_sections', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->text('icon_svg_path');
            $table->string('icon_color_class')->default('text-indigo-400');
            $table->json('route_patterns')->nullable();
            $table->string('behavior')->default('collapsible'); // collapsible | single_link
            $table->string('route_name')->nullable();
            $table->string('role')->nullable();
            $table->string('permission')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index(['active', 'sort_order'], 'menu_sections_active_sort_idx');
        });

        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_section_id')->constrained('menu_sections')->cascadeOnDelete();
            $table->string('name');
            $table->string('route_name');
            $table->string('active_route_pattern')->nullable();
            $table->text('icon_svg_path')->nullable();
            $table->string('icon_color_class')->nullable();
            $table->string('permission')->nullable();
            $table->string('role')->nullable();
            $table->string('badge_key')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index(['menu_section_id', 'active', 'sort_order'], 'menu_items_section_active_sort_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_items');
        Schema::dropIfExists('menu_sections');
    }
};
