<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_teams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(
                ['business_id', 'deleted_at', 'name'],
                'event_teams_business_deleted_name_idx'
            );
            $table->index(
                ['business_id', 'deleted_at', 'active'],
                'event_teams_business_deleted_active_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_teams');
    }
};
