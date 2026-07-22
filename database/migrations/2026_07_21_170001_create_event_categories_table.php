<?php

use App\Enums\EventCategoryType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', array_column(EventCategoryType::cases(), 'value'));
            $table->boolean('general')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(
                ['business_id', 'deleted_at', 'name'],
                'event_categories_business_deleted_name_idx'
            );
            $table->index(
                ['business_id', 'deleted_at', 'type'],
                'event_categories_business_deleted_type_idx'
            );
            $table->index(
                ['general', 'deleted_at', 'type'],
                'event_categories_general_deleted_type_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_categories');
    }
};
