<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_attendee_type', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('attendee_type_id')
                ->constrained('attendee_types')
                ->cascadeOnDelete();
            $table->unsignedInteger('attendance')->default(0);
            $table->timestamps();

            $table->unique(
                ['event_id', 'attendee_type_id'],
                'event_attendee_type_unique_idx'
            );
            $table->index(
                ['attendee_type_id', 'event_id'],
                'event_attendee_type_reverse_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_attendee_type');
    }
};
