<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_team_role', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('event_team_role_id')
                ->constrained('event_team_roles')
                ->cascadeOnDelete();
            $table->timestamps();

            $table->unique(
                ['event_team_id', 'event_team_role_id'],
                'event_team_role_unique_idx'
            );
            $table->index(
                ['event_team_role_id', 'event_team_id'],
                'event_team_role_reverse_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_team_role');
    }
};
