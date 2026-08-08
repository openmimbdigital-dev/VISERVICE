<?php

use App\Enums\DocumentType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('address')->nullable();
            $table->string('profile_photo')->nullable();
            $table->boolean('status')->default(true);
            $table->enum('document_type', array_column(DocumentType::cases(), 'value'))->nullable();
            $table->string('document_number', 30)->nullable();
            $table->foreignId('city_id')->nullable()->constrained('cities')->nullOnDelete();
            $table->foreignId('country_id')->nullable()->constrained('countries')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(
                ['business_id', 'deleted_at', 'created_at'],
                'participants_business_deleted_created_idx'
            );
            $table->index(
                ['business_id', 'deleted_at', 'status'],
                'participants_business_deleted_status_idx'
            );
            $table->index(
                ['business_id', 'deleted_at', 'last_name', 'first_name'],
                'participants_business_deleted_name_idx'
            );
            $table->index(
                ['business_id', 'document_number', 'deleted_at'],
                'participants_business_document_deleted_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('participants');
    }
};
