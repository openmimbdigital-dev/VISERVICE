<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bank_accounts', function (Blueprint $table) {
            $table->foreignId('bank_id')->nullable()->after('id')->constrained('banks')->nullOnDelete();
            $table->dropColumn('bank_name');
        });
    }

    public function down(): void
    {
        Schema::table('bank_accounts', function (Blueprint $table) {
            $table->dropForeign(['bank_id']);
            $table->dropColumn('bank_id');
            $table->string('bank_name')->after('id');
        });
    }
};
