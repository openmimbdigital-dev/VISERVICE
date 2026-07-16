<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('general_configs', function (Blueprint $table) {
            $table->dropUnique('general_configs_business_configurable_key_uidx');

            // Varios documentos asociados pueden compartir la misma key por negocio
            $table->unique(
                ['business_id', 'key', 'label'],
                'general_configs_business_key_label_uidx'
            );
        });
    }

    public function down(): void
    {
        Schema::table('general_configs', function (Blueprint $table) {
            $table->dropUnique('general_configs_business_key_label_uidx');

            $table->unique(
                ['business_id', 'configurable_type', 'configurable_id', 'key'],
                'general_configs_business_configurable_key_uidx'
            );
        });
    }
};
