<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_team_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('event_team_id');
            $table->unsignedBigInteger('event_team_role_id');
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign(
                ['event_team_id', 'event_team_role_id'],
                'event_team_members_team_role_fk'
            )
                ->references(['event_team_id', 'event_team_role_id'])
                ->on('event_team_role')
                ->cascadeOnDelete();
            $table->unique(
                ['event_team_id', 'event_team_role_id', 'user_id'],
                'event_team_members_unique_idx'
            );
            $table->index(
                ['business_id', 'deleted_at', 'event_team_id'],
                'event_team_members_business_deleted_team_idx'
            );
            $table->index(
                ['event_team_id', 'deleted_at', 'event_team_role_id'],
                'event_team_members_team_deleted_role_idx'
            );
            $table->index(
                ['user_id', 'deleted_at'],
                'event_team_members_user_deleted_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_team_members');
    }
};
