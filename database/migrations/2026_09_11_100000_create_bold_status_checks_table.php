<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Constancia de cada vez que le preguntamos a Bold por un cobro.
 *
 * Confirmar un pago por consulta a la API es tan definitivo como hacerlo por el
 * webhook: activa la suscripción y genera la factura. Pero mientras del webhook
 * guardábamos todo, de la consulta no quedaba nada, así que ante un cobro dado
 * por pagado no había manera de responder la única pregunta que importa: en qué
 * nos basamos para darlo por pagado.
 *
 * Aquí queda la respuesta de Bold tal como la recibimos, desde dónde se preguntó
 * y qué se decidió con ella —incluidas las veces en que se decidió NO confirmar,
 * que son las que explican un cobro que sigue pendiente.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bold_status_checks', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('subscription_invoice_id')->nullable();
            $table->string('payment_link', 60)->nullable();
            $table->string('reference', 80)->nullable();

            $table->string('origin', 20)
                ->comment('callback = el pagador volvió del checkout; tarea, manual, diagnostico');

            // --- Lo que contestó Bold -------------------------------------
            $table->unsignedSmallInteger('http_status')->nullable();
            $table->string('reported_status', 30)->nullable()->comment('ACTIVE, PROCESSING, PAID, EXPIRED…');
            $table->decimal('reported_amount', 14, 2)->nullable();
            $table->string('reported_currency', 3)->nullable();
            $table->string('transaction_id', 80)->nullable();
            $table->string('payment_method', 40)->nullable();
            $table->boolean('is_sandbox')->nullable();
            $table->longText('raw_response')->nullable()->comment('La respuesta completa, tal cual');
            $table->string('error')->nullable()->comment('Si ni siquiera se pudo consultar');

            // --- Qué hicimos con eso --------------------------------------
            $table->boolean('confirmed')->default(false)
                ->comment('Si esta consulta fue la que dio el cobro por pagado');
            $table->text('decision')->nullable()->comment('Qué se decidió y por qué');
            $table->unsignedInteger('duration_ms')->nullable();

            $table->unsignedBigInteger('user_id')->nullable()->comment('Quién la provocó, si había sesión');
            $table->string('ip', 45)->nullable();
            $table->timestamps();

            $table->foreign('subscription_invoice_id', 'bold_checks_invoice_fk')
                ->references('id')->on('subscription_invoices')->nullOnDelete();
            $table->foreign('user_id', 'bold_checks_user_fk')
                ->references('id')->on('users')->nullOnDelete();

            $table->index(['subscription_invoice_id', 'created_at'], 'bold_checks_invoice_created_idx');
            $table->index('reported_status', 'bold_checks_status_idx');
            $table->index('confirmed', 'bold_checks_confirmed_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bold_status_checks');
    }
};
