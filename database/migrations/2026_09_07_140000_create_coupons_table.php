<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('code', 40)->comment('Código que digita el usuario, siempre en mayúsculas');
            $table->string('name', 120)->nullable()->comment('Nombre interno para reconocerlo en el listado');
            $table->enum('discount_type', ['percentage', 'amount'])
                ->comment('Cómo se expresa el descuento: porcentaje sobre el subtotal o valor fijo');
            $table->decimal('discount_value', 14, 2)
                ->comment('Porcentaje (0-100) o valor en pesos, según discount_type');
            $table->decimal('min_order_amount', 14, 2)->nullable()
                ->comment('Subtotal mínimo de la OT para poder aplicarlo');
            $table->date('starts_at')->nullable();
            $table->date('ends_at')->nullable();
            $table->unsignedInteger('max_uses')->nullable()
                ->comment('Cuántas OT pueden usarlo en total; nulo = sin límite');
            $table->boolean('active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            // El código solo tiene que ser único dentro del negocio: dos negocios
            // distintos pueden tener su propio «BIENVENIDA».
            $table->unique(['business_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
