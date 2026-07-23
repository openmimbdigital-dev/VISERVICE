<?php

use App\Support\DatabaseSchema;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        DatabaseSchema::updateEnumColumn(
            'subscriptions',
            'status',
            ['pending', 'trial', 'active', 'past_due', 'cancelled', 'expired'],
            'active'
        );
    }

    public function down(): void
    {
        DatabaseSchema::updateEnumColumn(
            'subscriptions',
            'status',
            ['trial', 'active', 'past_due', 'cancelled', 'expired'],
            'active'
        );
    }
};
