<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_order_associated_documents', function (Blueprint $table) {
            if (! Schema::hasColumn('work_order_associated_documents', 'associated_document_type_id')) {
                $table->unsignedBigInteger('associated_document_type_id')->after('work_order_id');

                $table->foreign('associated_document_type_id', 'wo_associated_documents_type_id_fk')
                    ->references('id')->on('associated_document_types')
                    ->restrictOnDelete();

                $table->unique(
                    ['work_order_id', 'associated_document_type_id'],
                    'wo_associated_documents_wo_type_uidx'
                );
            }

            if (! Schema::hasColumn('work_order_associated_documents', 'send_invoice')) {
                $table->boolean('send_invoice')->default(false)->after('value')
                    ->comment('Indica si el valor se envía a facturación (DIAN) al momento del registro');
            }
        });
    }

    public function down(): void
    {
        Schema::table('work_order_associated_documents', function (Blueprint $table) {
            if (Schema::hasColumn('work_order_associated_documents', 'associated_document_type_id')) {
                $table->dropUnique('wo_associated_documents_wo_type_uidx');
                $table->dropForeign('wo_associated_documents_type_id_fk');
                $table->dropColumn('associated_document_type_id');
            }

            if (Schema::hasColumn('work_order_associated_documents', 'send_invoice')) {
                $table->dropColumn('send_invoice');
            }
        });
    }
};
