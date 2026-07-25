<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_event_team', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('event_team_id')
                ->constrained('event_teams')
                ->cascadeOnDelete();
            $table->timestamps();

            $table->unique(
                ['event_id', 'event_team_id'],
                'event_event_team_unique_idx'
            );
            $table->index(
                ['event_team_id', 'event_id'],
                'event_event_team_reverse_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_event_team');
    }
};
