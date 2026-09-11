<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Llaves de Bold de cada negocio, para que cobre a su propia cuenta.
 *
 * Las suscripciones se cobran con las llaves de la plataforma porque ese dinero
 * es nuestro. Una factura de taller es al revés: la paga el cliente del taller y
 * el dinero es del taller, así que el cobro tiene que salir con sus llaves.
 *
 * Si un negocio no las tiene configuradas se usan las nuestras como respaldo.
 * Es una decisión deliberada para no dejar a nadie sin poder cobrar, pero tiene
 * una consecuencia que no se puede esconder: ese dinero entra a nuestra cuenta y
 * hay que girárselo después. Por eso cada cobro guarda a qué cuenta salió.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_bold_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id')->unique();

            $table->text('identity_key')->nullable()->comment('Autentica las llamadas a la API de Bold');
            $table->text('secret_key')->nullable()->comment('Verifica la firma de los webhooks');

            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->foreign('business_id')->references('id')->on('businesses')->cascadeOnDelete();
        });

        Schema::table('work_order_invoices', function (Blueprint $table) {
            $table->string('bold_payment_link', 60)->nullable()->after('dian_payment_means_code');
            $table->string('bold_link_url')->nullable()->after('bold_payment_link');
            $table->string('bold_reference', 80)->nullable()->after('bold_link_url');
            $table->string('bold_status', 20)->nullable()->after('bold_reference');
            $table->string('bold_payment_id', 80)->nullable()->after('bold_status');
            $table->string('bold_payment_method', 40)->nullable()->after('bold_payment_id');
            $table->string('bold_account', 20)->nullable()->after('bold_payment_method')
                ->comment('business = cuenta del negocio; platform = la nuestra, como respaldo');
            $table->timestamp('bold_link_created_at')->nullable()->after('bold_account');

            $table->index('bold_reference', 'work_order_invoices_bold_reference_idx');
        });
    }

    public function down(): void
    {
        Schema::table('work_order_invoices', function (Blueprint $table) {
            $table->dropIndex('work_order_invoices_bold_reference_idx');
            $table->dropColumn([
                'bold_payment_link',
                'bold_link_url',
                'bold_reference',
                'bold_status',
                'bold_payment_id',
                'bold_payment_method',
                'bold_account',
                'bold_link_created_at',
            ]);
        });

        Schema::dropIfExists('business_bold_settings');
    }
};
