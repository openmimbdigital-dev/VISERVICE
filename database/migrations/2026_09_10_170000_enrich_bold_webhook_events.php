<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Convierte la bitácora de Bold en un registro con el que se pueda investigar.
 *
 * Lo que había servía para saber que algo llegó, no para saber qué llegó ni qué
 * contestamos. Cuando lo que está en juego es un pago, eso no alcanza: hace falta
 * el cuerpo crudo tal cual, las cabeceras, de qué IP vino, con qué firma, cuánto
 * tardamos y —sobre todo— qué respondimos, porque de esa respuesta depende que
 * Bold reintente o dé la notificación por entregada.
 *
 * Cambia además la unicidad de «event_id» por un índice normal: cada entrega
 * queda registrada, incluidos los reintentos del mismo evento. La idempotencia
 * pasa a mirar si ese evento ya se procesó, no si ya se vio.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->dropEventIdUnique();

        Schema::table('bold_webhook_events', function (Blueprint $table) {
            // --- Cuándo y de dónde ---------------------------------------
            $table->timestamp('received_at', 3)->nullable()->after('id')
                ->comment('Momento exacto en que entró la petición');
            $table->string('ip', 45)->nullable()->after('received_at');
            $table->string('user_agent')->nullable()->after('ip');
            $table->string('http_method', 10)->nullable()->after('user_agent');
            $table->string('url')->nullable()->after('http_method');

            // --- Qué llegó, textualmente ---------------------------------
            $table->json('headers')->nullable()->after('url')
                ->comment('Cabeceras recibidas; las de credenciales van ocultas');
            $table->longText('raw_body')->nullable()->after('headers')
                ->comment('El cuerpo tal cual llegó, sin interpretar');
            $table->unsignedInteger('body_bytes')->nullable()->after('raw_body');

            // --- Firma ----------------------------------------------------
            $table->string('signature', 255)->nullable()->after('signature_valid')
                ->comment('La firma que vino en la cabecera');
            $table->string('signed_with', 60)->nullable()->after('signature')
                ->comment('Con cuál de nuestras llaves coincidió');

            // --- El dinero, a la vista ------------------------------------
            $table->decimal('amount', 14, 2)->nullable()->after('payment_id');
            $table->string('currency', 3)->nullable()->after('amount');
            $table->string('payment_method', 40)->nullable()->after('currency');
            $table->string('event_time', 40)->nullable()->after('payment_method')
                ->comment('La marca de tiempo que reporta Bold en el evento');

            // --- Qué contestamos ------------------------------------------
            $table->unsignedSmallInteger('response_status')->nullable()->after('result');
            $table->string('response_body', 500)->nullable()->after('response_status')
                ->comment('La respuesta exacta que recibió Bold');
            $table->unsignedInteger('duration_ms')->nullable()->after('response_body')
                ->comment('Cuánto tardamos; Bold espera menos de 2 segundos');

            $table->index('event_id', 'bold_events_event_id_idx');
            $table->index('reference', 'bold_events_reference_idx');
            $table->index('received_at', 'bold_events_received_idx');
        });

        // El motivo ya no cabe en 255 caracteres: ahora explica qué se intentó.
        Schema::table('bold_webhook_events', function (Blueprint $table) {
            $table->text('result')->nullable()->change();
        });

        $this->backfillArrivalTimes();
    }

    public function down(): void
    {
        Schema::table('bold_webhook_events', function (Blueprint $table) {
            $table->dropIndex('bold_events_event_id_idx');
            $table->dropIndex('bold_events_reference_idx');
            $table->dropIndex('bold_events_received_idx');

            $table->dropColumn([
                'received_at',
                'ip',
                'user_agent',
                'http_method',
                'url',
                'headers',
                'raw_body',
                'body_bytes',
                'signature',
                'signed_with',
                'amount',
                'currency',
                'payment_method',
                'event_time',
                'response_status',
                'response_body',
                'duration_ms',
            ]);

            $table->unique('event_id');
        });
    }

    /**
     * Quita la unicidad de «event_id» si sigue puesta: ahora cada entrega —y sus
     * reintentos— tiene su propia fila.
     */
    private function dropEventIdUnique(): void
    {
        $exists = DB::selectOne(
            "SHOW INDEX FROM bold_webhook_events WHERE Key_name = 'bold_webhook_events_event_id_unique'"
        );

        if (! $exists) {
            return;
        }

        Schema::table('bold_webhook_events', function (Blueprint $table) {
            $table->dropUnique('bold_webhook_events_event_id_unique');
        });
    }

    /** De lo ya registrado solo se puede recuperar la hora en que se guardó. */
    private function backfillArrivalTimes(): void
    {
        DB::table('bold_webhook_events')
            ->whereNull('received_at')
            ->update(['received_at' => DB::raw('created_at')]);
    }
};
