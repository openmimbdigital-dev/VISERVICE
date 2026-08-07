<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('participant_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(
                ['business_id', 'deleted_at', 'name'],
                'participant_roles_business_deleted_name_idx'
            );
            $table->index(
                ['business_id', 'deleted_at', 'active'],
                'participant_roles_business_deleted_active_idx'
            );
        });

        Schema::table('participants', function (Blueprint $table) {
            $table->foreignId('participant_role_id')
                ->nullable()
                ->after('business_id')
                ->constrained('participant_roles')
                ->nullOnDelete();

            $table->index(
                ['business_id', 'deleted_at', 'participant_role_id'],
                'participants_business_deleted_role_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            $table->dropIndex('participants_business_deleted_role_idx');
            $table->dropConstrainedForeignId('participant_role_id');
        });

        Schema::dropIfExists('participant_roles');
    }
};
