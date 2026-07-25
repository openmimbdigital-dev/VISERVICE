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
            $table->string('name');
            $table->text('description')->nullable();
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->unsignedInteger('attendance')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(
                ['business_id', 'deleted_at', 'date'],
                'events_business_deleted_date_idx'
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
