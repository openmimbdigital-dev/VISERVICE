<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('event_category_id')
                ->nullable()
                ->constrained('event_categories')
                ->nullOnDelete();
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('events')
                ->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->date('date_start');
            $table->date('date_end');
            $table->string('day');
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('active')->default(true);
            $table->boolean('multi_day')->default(false);
            $table->boolean('attendance_enabled')->default(true);
            $table->boolean('participation_enabled')->default(true);
            $table->boolean('attendance_closed')->default(false);
            $table->boolean('participation_closed')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(
                ['business_id', 'deleted_at', 'date_start'],
                'events_business_deleted_date_start_idx'
            );
            $table->index(
                ['business_id', 'deleted_at', 'event_category_id'],
                'events_business_deleted_category_idx'
            );
            $table->index(
                ['business_id', 'deleted_at', 'name'],
                'events_business_deleted_name_idx'
            );
            $table->index(
                ['event_category_id', 'deleted_at'],
                'events_category_deleted_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
