<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('team_positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('business_type_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('label');
            $table->boolean('active')->default(true);
            $table->boolean('general')->default(false)->comment('Registro del sistema; solo superAdmin puede editarlo o eliminarlo');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['business_id', 'name'], 'team_positions_business_name_idx');
            $table->index(['business_id', 'label'], 'team_positions_business_label_idx');
            $table->index(['business_type_id', 'deleted_at', 'active'], 'team_positions_type_deleted_active_idx');
            $table->index(['business_id', 'deleted_at', 'created_at'], 'team_positions_business_deleted_created_idx');
            $table->index(['general', 'deleted_at', 'active'], 'team_positions_general_deleted_active_idx');
            $table->index(['deleted_at', 'created_at'], 'team_positions_deleted_created_idx');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('team_position_id')
                ->nullable()
                ->after('country_id')
                ->constrained('team_positions')
                ->nullOnDelete();
            $table->string('name_team_position')
                ->nullable()
                ->after('team_position_id')
                ->comment('Nombre denormalizado del cargo al momento de asignación');

            $table->index('team_position_id', 'users_team_position_idx');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['team_position_id']);
            $table->dropIndex('users_team_position_idx');
            $table->dropColumn(['team_position_id', 'name_team_position']);
        });

        Schema::dropIfExists('team_positions');
    }
};
