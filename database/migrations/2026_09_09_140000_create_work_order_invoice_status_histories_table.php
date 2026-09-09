<?php

use App\Enums\ElectronicInvoiceStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Línea de tiempo de una factura de OT.
 *
 * Una factura avanza por dos ejes que no son el mismo: el de cobro
 * (pendiente → pagada / vencida / anulada, en «work_order_invoices.status») y el
 * de emisión electrónica (pendiente → enviada → validada / rechazada, en
 * «electronic_invoices.status»). Antes no había forma de ver ambos en orden, y
 * una factura ya emitida ante la DIAN seguía leyéndose solo como «pendiente».
 * Esta tabla los junta en una sola cronología sin mezclar los estados.
 */
return new class extends Migration
{
    private const KIND_BILLING = 'billing';

    private const KIND_EMISSION = 'emission';

    public function up(): void
    {
        Schema::create('work_order_invoice_status_histories', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('business_id');
            $table->unsignedBigInteger('work_order_invoice_id');

            $table->string('kind', 20)
                ->comment('billing = estado de cobro; emission = estado ante la DIAN');
            $table->string('from_status', 40)->nullable()
                ->comment('Estado anterior; null en el registro de apertura de cada eje');
            $table->string('to_status', 40)
                ->comment('Estado al que pasó la factura');
            $table->text('comment')->nullable()
                ->comment('Nota, motivo de rechazo o error del proveedor');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_name')->nullable()
                ->comment('Nombre del usuario al momento del cambio');
            $table->unsignedInteger('duration_seconds')->nullable()
                ->comment('Segundos que la factura permaneció en from_status dentro de su eje');
            $table->json('metadata')->nullable()
                ->comment('Datos extra (número autorizado, CUFE, transacción, origen…)');
            $table->timestamp('created_at')->nullable();

            // Los nombres van explícitos: los que Laravel deriva de este nombre de
            // tabla superan los 64 caracteres que admite MySQL.
            $table->foreign('business_id', 'wo_inv_status_hist_business_fk')
                ->references('id')->on('businesses')->cascadeOnDelete();
            $table->foreign('work_order_invoice_id', 'wo_inv_status_hist_invoice_fk')
                ->references('id')->on('work_order_invoices')->cascadeOnDelete();
            $table->foreign('user_id', 'wo_inv_status_hist_user_fk')
                ->references('id')->on('users')->nullOnDelete();

            $table->index(['work_order_invoice_id', 'created_at'], 'wo_inv_status_hist_invoice_created_idx');
            $table->index(['business_id', 'kind'], 'wo_inv_status_hist_business_kind_idx');
        });

        $this->backfill();
    }

    public function down(): void
    {
        Schema::dropIfExists('work_order_invoice_status_histories');
    }

    /**
     * Reconstruye la cronología de las facturas que ya existen a partir de las
     * fechas que cada una guarda. Las filas quedan marcadas en «metadata» para
     * distinguirlas de las capturadas en vivo.
     */
    private function backfill(): void
    {
        if (! Schema::hasTable('work_order_invoices')) {
            return;
        }

        $electronic = Schema::hasTable('electronic_invoices')
            ? DB::table('electronic_invoices')->get()->keyBy('work_order_invoice_id')
            : collect();

        $user_names = DB::table('users')
            ->select('id', 'first_name', 'last_name', 'username')
            ->get()
            ->keyBy('id');

        DB::table('work_order_invoices')->orderBy('id')->chunkById(200, function ($invoices) use ($electronic, $user_names) {
            $rows = [];

            foreach ($invoices as $invoice) {
                $entries = $this->entriesFor($invoice, $electronic->get($invoice->id), $user_names);

                // La permanencia se mide dentro de cada eje, no contra el otro.
                $previous_at = [];

                foreach ($entries as $entry) {
                    $changed_at = Carbon::parse($entry['created_at']);
                    $kind = $entry['kind'];

                    $rows[] = [
                        'business_id'           => $invoice->business_id,
                        'work_order_invoice_id' => $invoice->id,
                        'kind'                  => $kind,
                        'from_status'           => $entry['from_status'],
                        'to_status'             => $entry['to_status'],
                        'comment'               => $entry['comment'],
                        'user_id'               => $entry['user_id'],
                        'user_name'             => $this->nameOf($user_names->get($entry['user_id'])),
                        'duration_seconds'      => isset($previous_at[$kind])
                            ? max(0, (int) $previous_at[$kind]->diffInSeconds($changed_at))
                            : null,
                        'metadata'              => json_encode(
                            $entry['metadata'] + ['backfilled' => true],
                            JSON_UNESCAPED_UNICODE
                        ),
                        'created_at'            => $changed_at->toDateTimeString(),
                    ];

                    $previous_at[$kind] = $changed_at;
                }
            }

            foreach (array_chunk($rows, 500) as $chunk) {
                DB::table('work_order_invoice_status_histories')->insert($chunk);
            }
        });
    }

    /**
     * @param  \Illuminate\Support\Collection<int, object>  $user_names
     * @return list<array<string, mixed>>
     */
    private function entriesFor(object $invoice, ?object $electronic, $user_names): array
    {
        $entries = [];

        // Eje de cobro: la factura nace pendiente.
        $entries[] = [
            'kind'        => self::KIND_BILLING,
            'from_status' => null,
            'to_status'   => 'pendiente',
            'comment'     => null,
            'user_id'     => $invoice->created_by ?? null,
            'created_at'  => $invoice->created_at ?? now(),
            'metadata'    => ['source' => 'work_order_invoices'],
        ];

        // Eje de emisión: se reconstruye con las marcas de tiempo del documento.
        if ($electronic) {
            $emission_from = null;

            $entries[] = [
                'kind'        => self::KIND_EMISSION,
                'from_status' => null,
                'to_status'   => ElectronicInvoiceStatus::Pending->value,
                'comment'     => null,
                'user_id'     => $electronic->created_by ?? null,
                'created_at'  => $electronic->issued_at ?? $electronic->created_at ?? $invoice->created_at ?? now(),
                'metadata'    => ['source' => 'electronic_invoices', 'document_number' => $electronic->document_number ?? null],
            ];
            $emission_from = ElectronicInvoiceStatus::Pending->value;

            if (! empty($electronic->sent_at)) {
                $entries[] = [
                    'kind'        => self::KIND_EMISSION,
                    'from_status' => $emission_from,
                    'to_status'   => ElectronicInvoiceStatus::Sent->value,
                    'comment'     => null,
                    'user_id'     => $electronic->created_by ?? null,
                    'created_at'  => $electronic->sent_at,
                    'metadata'    => [
                        'source'          => 'electronic_invoices',
                        'document_number' => $electronic->document_number ?? null,
                        'transaction_id'  => $electronic->transaction_id ?? null,
                        'cufe'            => $electronic->cufe ?? null,
                    ],
                ];
                $emission_from = ElectronicInvoiceStatus::Sent->value;
            }

            if (! empty($electronic->accepted_at)) {
                $entries[] = [
                    'kind'        => self::KIND_EMISSION,
                    'from_status' => $emission_from,
                    'to_status'   => ElectronicInvoiceStatus::Accepted->value,
                    'comment'     => null,
                    'user_id'     => $electronic->created_by ?? null,
                    'created_at'  => $electronic->accepted_at,
                    'metadata'    => ['source' => 'electronic_invoices'],
                ];
                $emission_from = ElectronicInvoiceStatus::Accepted->value;
            }

            // Un rechazo o un error de emisión no dejan marca de tiempo propia:
            // se ubican en la última consulta de estado que se hizo.
            $current = (string) ($electronic->status ?? '');

            if ($current !== $emission_from && in_array($current, [
                ElectronicInvoiceStatus::Rejected->value,
                ElectronicInvoiceStatus::Error->value,
            ], true)) {
                $entries[] = [
                    'kind'        => self::KIND_EMISSION,
                    'from_status' => $emission_from,
                    'to_status'   => $current,
                    'comment'     => $electronic->error_message ?? null,
                    'user_id'     => $electronic->created_by ?? null,
                    'created_at'  => $electronic->status_checked_at
                        ?? $electronic->updated_at
                        ?? $electronic->sent_at
                        ?? $invoice->created_at
                        ?? now(),
                    'metadata'    => ['source' => 'electronic_invoices', 'error_id' => $electronic->error_id ?? null],
                ];
            }
        }

        // Cierre del eje de cobro, si la factura ya no está pendiente.
        if ($invoice->status !== 'pendiente') {
            $entries[] = [
                'kind'        => self::KIND_BILLING,
                'from_status' => 'pendiente',
                'to_status'   => (string) $invoice->status,
                'comment'     => null,
                'user_id'     => $invoice->created_by ?? null,
                'created_at'  => $invoice->paid_at ?? $invoice->updated_at ?? $invoice->created_at ?? now(),
                'metadata'    => ['source' => 'work_order_invoices'],
            ];
        }

        usort($entries, fn ($a, $b) => strcmp((string) $a['created_at'], (string) $b['created_at']));

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
