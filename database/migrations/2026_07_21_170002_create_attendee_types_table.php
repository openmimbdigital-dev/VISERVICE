<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendee_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedInteger('minimum_range');
            $table->unsignedInteger('maximum_range');
            $table->boolean('general')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(
                ['business_id', 'deleted_at', 'name'],
                'attendee_types_business_deleted_name_idx'
            );
            $table->index(
                ['business_id', 'deleted_at', 'minimum_range', 'maximum_range'],
                'attendee_types_business_deleted_range_idx'
            );
            $table->index(
                ['general', 'deleted_at', 'name'],
                'attendee_types_general_deleted_name_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendee_types');
    }
};
