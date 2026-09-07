<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_dian_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->unique()->constrained()->cascadeOnDelete();

            $table->string('provider', 30)->default('titanio')
                ->comment('Proveedor tecnológico de facturación electrónica');
            $table->enum('environment', ['test', 'production'])->default('test');

            // Identificadores que entrega el proveedor al registrar el emisor.
            $table->unsignedBigInteger('tr_tipo_id')->nullable()
                ->comment('Perfil de emisión individual en la plataforma del proveedor');
            $table->unsignedBigInteger('cfg_lote_id')->nullable()
                ->comment('Perfil de emisión por lote (uso futuro)');
            $table->string('profile_name')->nullable()
                ->comment('Nombre del perfil creado en la plataforma del proveedor');

            // Resolución de facturación DIAN.
            $table->string('resolution_number', 30)->nullable()
                ->comment('Número de autorización de la resolución DIAN');
            $table->string('prefix', 10)->nullable();
            $table->unsignedBigInteger('range_from')->nullable();
            $table->unsignedBigInteger('range_to')->nullable();
            $table->date('valid_from')->nullable();
            $table->date('valid_to')->nullable();
            $table->text('technical_key')->nullable()->comment('Clave técnica DIAN (cifrada)');

            $table->unsignedBigInteger('next_consecutive')->nullable()
                ->comment('Próximo número a emitir; si es null se arranca en range_from');

            $table->string('software_id')->nullable();
            $table->text('software_pin')->nullable()->comment('PIN del software (cifrado)');

            // Opciones de entrega al adquiriente (bloque REC del documento).
            $table->boolean('notify_customer')->default(true);
            $table->boolean('include_pdf')->default(true);
            $table->boolean('include_xml')->default(true);
            $table->boolean('include_attachments')->default(false);

            $table->boolean('active')->default(false)
                ->comment('Habilita la emisión electrónica para el negocio');
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['business_id', 'active'], 'business_dian_settings_business_active_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_dian_settings');
    }
};
