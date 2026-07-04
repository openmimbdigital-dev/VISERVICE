<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('business_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('label');
            $table->boolean('status')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('businesses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('address')->nullable();
            $table->string('email')->nullable();
            $table->string('slug')->unique();
            $table->string('nit')->unique();
            $table->string('logo')->nullable();
            $table->string('website')->nullable();
            $table->string('facebook')->nullable();
            $table->string('instagram')->nullable();
            $table->string('twitter')->nullable();
            $table->string('phone_number')->nullable();
            $table->unsignedBigInteger('city_id')->nullable();
            $table->unsignedBigInteger('country_id')->nullable();
            $table->unsignedBigInteger('business_type_id');
            $table->unsignedBigInteger('business_id')->nullable();
            $table->json('representative')->nullable();
            $table->json('configurations')->nullable();
            $table->json('configurations_value')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->index('name', 'businesses_name_idx');
            // Claves foráneas
            $table->foreign('city_id')->references('id')->on('cities')->onDelete('set null');
            $table->foreign('country_id')->references('id')->on('countries')->onDelete('set null');
            $table->foreign('business_type_id')->references('id')->on('business_types')->onDelete('cascade');
        });


        Schema::create('business_addresses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id')->comment('ID del negocio al que pertenece esta dirección');
            $table->string('name')->nullable()->comment('Nombre de la dirección (ej: "Sede Principal", "Sucursal Norte")');
            $table->string('address_line_1')->comment('Dirección principal');
            $table->string('address_line_2')->nullable()->comment('Dirección secundaria (apto, torre, etc.)');
            $table->string('neighborhood')->nullable()->comment('Barrio o sector');
            $table->string('postal_code')->nullable()->comment('Código postal');
            $table->unsignedBigInteger('city_id')->nullable()->comment('ID de la ciudad');
            $table->unsignedBigInteger('country_id')->nullable()->comment('ID del país');
            $table->decimal('latitude', 10, 8)->nullable()->comment('Latitud para mapas');
            $table->decimal('longitude', 11, 8)->nullable()->comment('Longitud para mapas');
            $table->string('phone_number')->nullable()->comment('Teléfono específico de esta dirección');
            $table->string('email')->nullable()->comment('Email específico de esta dirección');
            $table->text('notes')->nullable()->comment('Notas adicionales');
            $table->boolean('is_primary')->default(false)->comment('Si es la dirección principal');
            $table->boolean('is_active')->default(true)->comment('Si está activa');
            $table->timestamps();
            $table->softDeletes();

            // Claves foráneas
            $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade');
            $table->foreign('city_id')->references('id')->on('cities')->onDelete('set null');
            $table->foreign('country_id')->references('id')->on('countries')->onDelete('set null');

            // Índices
            $table->index('business_id');
            $table->index('city_id');
            $table->index('country_id');
            $table->index('is_primary');
            $table->index('is_active');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('email_verified_at');
            $table->dropColumn('name');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('username')->unique();
            $table->string('email')->nullable()->change();
            $table->string('phone_number')->nullable();
            $table->string('address')->nullable();
            $table->string('profile_photo')->nullable();
            $table->boolean('status')->default(true);
            $table->enum('document_type', [
                'CC',   // Cédula de Ciudadanía
                'TI',   // Tarjeta de Identidad
                'RC',   // Registro Civil de Nacimiento
                'CE',   // Cédula de Extranjería
                'PA',   // Pasaporte
                'PEP',  // Permiso Especial de Permanencia (ya reemplazado por PPT)
                'PPT',  // Permiso por Protección Temporal
                'NIT',  // Número de Identificación Tributaria (para personas jurídicas o comerciantes)
                'NUIP', // Número Único de Identificación Personal (se usa en menores)
                'CN',   // Certificado de Nacido Vivo (en temas de salud)
                'MS',   // Menor sin Identificación (casos especiales en EPS/IPS)
                'AS',   // Adulto sin Identificación (casos especiales en EPS/IPS)
                'CD'    // Carné Diplomático
            ])->nullable();
            $table->integer('document_number')->nullable();
            $table->unsignedBigInteger('city_id')->nullable();
            $table->unsignedBigInteger('country_id')->nullable();
            $table->softDeletes();

            // Listado usuarios (superAdmin) y filtros por estado/fecha
            $table->index(['deleted_at', 'status', 'created_at'], 'users_deleted_status_created_idx');

            // Índices para las claves foráneas
            $table->index('city_id');
            $table->index('country_id');

            // Claves foráneas
            $table->foreign('city_id')->references('id')->on('cities')->onDelete('set null');
            $table->foreign('country_id')->references('id')->on('countries')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_types');
        Schema::dropIfExists('businesses');
        Schema::dropIfExists('business_addresses');
    }
};
