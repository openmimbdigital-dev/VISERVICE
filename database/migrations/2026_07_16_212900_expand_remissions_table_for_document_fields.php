<?php

use App\Support\DatabaseSchema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Alinea la tabla remissions ya existente con el esquema completo del documento.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('remissions', function (Blueprint $table) {
            if (! Schema::hasColumn('remissions', 'client_id')) {
                $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            }

            if (! Schema::hasColumn('remissions', 'equipment_id')) {
                $table->foreignId('equipment_id')->nullable()->constrained('equipment')->nullOnDelete();
            }

            if (! Schema::hasColumn('remissions', 'type')) {
                $table->enum('type', ['entrega', 'devolucion', 'traslado'])->default('entrega');
            }

            if (! Schema::hasColumn('remissions', 'quotation_or_po_reference')) {
                $table->string('quotation_or_po_reference')->nullable();
            }

            if (! Schema::hasColumn('remissions', 'issue_date')) {
                $table->date('issue_date')->nullable();
            }

            if (! Schema::hasColumn('remissions', 'delivery_address')) {
                $table->string('delivery_address')->nullable();
                $table->string('delivery_city')->nullable();
                $table->string('delivery_contact')->nullable();
                $table->string('delivery_phone', 50)->nullable();
                $table->text('delivery_observations')->nullable();
            }

            if (! Schema::hasColumn('remissions', 'observations')) {
                $table->text('observations')->nullable();
            }

            if (! Schema::hasColumn('remissions', 'delivered_by_name')) {
                $table->string('delivered_by_name')->nullable();
                $table->string('delivered_by_position')->nullable();
                $table->string('delivered_by_document', 50)->nullable();
            }

            if (! Schema::hasColumn('remissions', 'delivered_by_signature')) {
                $table->string('delivered_by_signature')->nullable();
            }

            if (! Schema::hasColumn('remissions', 'received_by_name')) {
                $table->string('received_by_name')->nullable();
                $table->string('received_by_position')->nullable();
                $table->string('received_by_document', 50)->nullable();
                $table->timestamp('received_at')->nullable();
                $table->string('received_by_signature')->nullable();
            }

            if (! Schema::hasColumn('remissions', 'total_items')) {
                $table->unsignedInteger('total_items')->default(0);
            }
        });

        if (Schema::hasColumn('remissions', 'notes') && Schema::hasColumn('remissions', 'observations')) {
            DB::statement('UPDATE remissions SET observations = notes WHERE observations IS NULL AND notes IS NOT NULL');

            Schema::table('remissions', function (Blueprint $table) {
                $table->dropColumn('notes');
            });
        }

        if (Schema::hasColumn('remissions', 'items')) {
            Schema::table('remissions', function (Blueprint $table) {
                $table->dropColumn('items');
            });
        }

        DatabaseSchema::addIndexIfMissing('remissions', 'remissions_business_deleted_type_idx', ['business_id', 'deleted_at', 'type']);
        DatabaseSchema::addIndexIfMissing('remissions', 'remissions_client_deleted_idx', ['client_id', 'deleted_at']);
        DatabaseSchema::addIndexIfMissing('remissions', 'remissions_equipment_deleted_idx', ['equipment_id', 'deleted_at']);
    }

    public function down(): void
    {
        Schema::table('remissions', function (Blueprint $table) {
            foreach (['remissions_business_deleted_type_idx', 'remissions_client_deleted_idx', 'remissions_equipment_deleted_idx'] as $index) {
                if (DatabaseSchema::hasIndex('remissions', $index)) {
                    $table->dropIndex($index);
                }
            }
        });

        Schema::table('remissions', function (Blueprint $table) {
            $drop = [];

            foreach ([
                'client_id', 'equipment_id', 'type', 'quotation_or_po_reference', 'issue_date',
                'delivery_address', 'delivery_city', 'delivery_contact', 'delivery_phone', 'delivery_observations',
                'observations', 'delivered_by_name', 'delivered_by_position', 'delivered_by_document',
                'delivered_by_signature', 'received_by_name', 'received_by_position', 'received_by_document',
                'received_at', 'received_by_signature', 'total_items',
            ] as $column) {
                if (Schema::hasColumn('remissions', $column)) {
                    $drop[] = $column;
                }
            }

            if ($drop !== []) {
                $table->dropColumn($drop);
            }

            if (! Schema::hasColumn('remissions', 'notes')) {
                $table->text('notes')->nullable();
            }

            if (! Schema::hasColumn('remissions', 'items')) {
                $table->json('items')->nullable();
            }
        });
    }
};
