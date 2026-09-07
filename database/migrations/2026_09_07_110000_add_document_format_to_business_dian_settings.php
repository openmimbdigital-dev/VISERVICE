<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('business_dian_settings', function (Blueprint $table) {
            $table->enum('document_format', ['json', 'xml'])->default('json')->after('provider')
                ->comment('Formato de entrada del perfil en el proveedor: JSON_DATASET o DATASET_DATASET');
        });

        Schema::table('electronic_invoices', function (Blueprint $table) {
            $table->renameColumn('request_xml', 'request_document');
        });
    }

    public function down(): void
    {
        Schema::table('electronic_invoices', function (Blueprint $table) {
            $table->renameColumn('request_document', 'request_xml');
        });

        Schema::table('business_dian_settings', function (Blueprint $table) {
            $table->dropColumn('document_format');
        });
    }
};
