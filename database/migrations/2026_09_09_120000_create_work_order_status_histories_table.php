<?php

use App\Enums\WorkOrderStatus;
use App\Models\WorkOrder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Bitácora dedicada de los cambios de estado de una orden de trabajo.
 *
 * Hasta ahora el rastro vivía repartido entre «user_historical» (registro genérico
 * de auditoría) y la columna JSON «work_orders.status_comments», que solo guardaba
 * los cambios que traían comentario. Esta tabla concentra la línea de tiempo
 * completa y le agrega el tiempo de permanencia en cada estado.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_order_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete()
                ->comment('Comercio dueño de la OT');
            $table->foreignId('work_order_id')->constrained()->cascadeOnDelete();
            $table->string('from_status', 40)->nullable()
                ->comment('Estado anterior; null en el registro de apertura');
            $table->string('to_status', 40)
                ->comment('Estado al que pasó la OT');
            $table->text('comment')->nullable()
                ->comment('Nota del cambio o motivo de la cancelación');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('user_name')->nullable()
                ->comment('Nombre del usuario al momento del cambio, por si luego se elimina');
            $table->unsignedInteger('duration_seconds')->nullable()
                ->comment('Segundos que la OT permaneció en from_status');
            $table->json('metadata')->nullable()
                ->comment('Datos extra del cambio (equipos, origen del registro…)');
            $table->timestamp('created_at')->nullable();

            $table->index(['work_order_id', 'created_at'], 'wo_status_histories_wo_created_index');
            $table->index(['business_id', 'to_status'], 'wo_status_histories_business_status_index');
        });

        $this->backfill();
    }

    public function down(): void
    {
        Schema::dropIfExists('work_order_status_histories');
    }

    /**
     * Reconstruye la línea de tiempo de las OT que ya existen.
     *
     * La fuente principal es «user_historical»; cuando una OT no dejó rastro allí
     * se sintetiza a partir de las fechas de la propia OT. Las filas reconstruidas
     * quedan marcadas en «metadata» para no confundirlas con las reales.
     */
    private function backfill(): void
    {
        if (! Schema::hasTable('work_orders') || ! Schema::hasTable('user_historical')) {
            return;
        }

        $logs = DB::table('user_historical')
            ->where('subject_type', WorkOrder::class)
            ->whereIn('action', ['created', 'status_changed'])
            ->where('module', 'workshop.work-orders')
            ->orderBy('subject_id')
            ->orderBy('created_at')
            ->get()
            ->groupBy('subject_id');

        $user_names = DB::table('users')
            ->select('id', 'first_name', 'last_name', 'username')
            ->get()
            ->keyBy('id');

        DB::table('work_orders')->orderBy('id')->chunkById(200, function ($work_orders) use ($logs, $user_names) {
            $rows = [];

            foreach ($work_orders as $work_order) {
                $entries = $this->entriesFor($work_order, $logs->get($work_order->id), $user_names);
                $previous_at = null;

                foreach ($entries as $entry) {
                    $changed_at = Carbon::parse($entry['created_at']);

                    $rows[] = [
                        'business_id' => $work_order->business_id,
                        'work_order_id' => $work_order->id,
                        'from_status' => $entry['from_status'],
                        'to_status' => $entry['to_status'],
                        'comment' => $entry['comment'],
                        'user_id' => $entry['user_id'],
                        'user_name' => $entry['user_name'],
                        'duration_seconds' => $previous_at
                            ? max(0, (int) $previous_at->diffInSeconds($changed_at))
                            : null,
                        'metadata' => json_encode($entry['metadata'], JSON_UNESCAPED_UNICODE),
                        'created_at' => $changed_at->toDateTimeString(),
                    ];

                    $previous_at = $changed_at;
                }
            }

            if ($rows !== []) {
                foreach (array_chunk($rows, 500) as $chunk) {
                    DB::table('work_order_status_histories')->insert($chunk);
                }
            }
        });
    }

    /**
     * @param  Collection<int, object>|null  $logs
     * @param  Collection<int, object>  $user_names
     * @return list<array<string, mixed>>
     */
    private function entriesFor(object $work_order, $logs, $user_names): array
    {
        $entries = [];
        $current_status = null;

        foreach ($logs ?? [] as $log) {
            $properties = json_decode((string) $log->properties, true) ?: [];
            $user = $user_names->get($log->user_id);

            if ($log->action === 'created' && $entries === []) {
                $to_status = (string) ($properties['status'] ?? WorkOrderStatus::Created->value);
                $entries[] = [
                    'from_status' => null,
                    'to_status' => $to_status,
                    'comment' => null,
                    'user_id' => $log->user_id,
                    'user_name' => $this->nameOf($user),
                    'created_at' => $log->created_at,
                    'metadata' => ['backfilled' => true, 'source' => 'user_historical'],
                ];
                $current_status = $to_status;

                continue;
            }

            if ($log->action !== 'status_changed') {
                continue;
            }

            $to_status = (string) ($properties['to'] ?? '');

            if ($to_status === '') {
                continue;
            }

            $entries[] = [
                'from_status' => $properties['from'] ?? $current_status,
                'to_status' => $to_status,
                'comment' => $properties['comment'] ?? null,
                'user_id' => $log->user_id,
                'user_name' => $this->nameOf($user),
                'created_at' => $log->created_at,
                'metadata' => ['backfilled' => true, 'source' => 'user_historical'],
            ];
            $current_status = $to_status;
        }

        // OT sin rastro en la auditoría: se deja al menos la apertura y, si su estado
        // actual ya no es el inicial, el salto al estado en el que está hoy.
        if ($entries === []) {
            $opening = $work_order->status === WorkOrderStatus::Draft->value
                ? WorkOrderStatus::Draft->value
                : WorkOrderStatus::Created->value;

            $entries[] = [
                'from_status' => null,
                'to_status' => $opening,
                'comment' => null,
                'user_id' => $work_order->created_by ?? null,
                'user_name' => $this->nameOf($user_names->get($work_order->created_by)),
                'created_at' => $work_order->created_at ?? now(),
                'metadata' => ['backfilled' => true, 'source' => 'work_orders'],
            ];
            $current_status = $opening;
        }

        if ($current_status !== $work_order->status) {
            $entries[] = [
                'from_status' => $current_status,
                'to_status' => (string) $work_order->status,
                'comment' => null,
                'user_id' => $work_order->created_by ?? null,
                'user_name' => $this->nameOf($user_names->get($work_order->created_by)),
                'created_at' => $work_order->finalized_at ?? $work_order->updated_at ?? $work_order->created_at ?? now(),
                'metadata' => ['backfilled' => true, 'source' => 'work_orders'],
            ];
        }

        return $entries;
    }

    private function nameOf(?object $user): ?string
    {
        if (! $user) {
            return null;
        }

        $name = trim(($user->first_name ?? '').' '.($user->last_name ?? ''));

        return $name !== '' ? $name : ($user->username ?? null);
    }
};
