<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->morphs('attendable'); // attendable_type, attendable_id + index
            $table->date('date_event');
            $table->time('attendance_hour')->nullable();
            $table->boolean('attendance')->default(false);
            $table->timestamps();
            $table->softDeletes();

            // Listado/filtro de asistencia por evento y fecha
            $table->index(
                ['event_id', 'deleted_at', 'date_event'],
                'event_attendances_event_deleted_date_idx'
            );

            // Asistencia de una persona (user/participant) por evento
            $table->index(
                ['attendable_type', 'attendable_id', 'deleted_at', 'event_id'],
                'event_attendances_attendable_deleted_event_idx'
            );

            // Una asistencia por persona, evento y fecha (soft-delete aware)
            $table->unique(
                ['event_id', 'attendable_type', 'attendable_id', 'date_event', 'deleted_at'],
                'event_attendances_event_attendable_date_unique_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_attendances');
    }
};
