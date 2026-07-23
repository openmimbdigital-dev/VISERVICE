<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->updateStatusEnum([
            'pending',
            'trial',
            'active',
            'past_due',
            'cancelled',
            'expired',
        ]);
    }

    public function down(): void
    {
        $this->updateStatusEnum([
            'trial',
            'active',
            'past_due',
            'cancelled',
            'expired',
        ]);
    }

    /** @param  list<string>  $values */
    private function updateStatusEnum(array $values): void
    {
        $driver = Schema::getConnection()->getDriverName();
        $quoted = implode(',', array_map(fn (string $value) => "'{$value}'", $values));

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE subscriptions MODIFY COLUMN status ENUM({$quoted}) DEFAULT 'active'");

            return;
        }

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE subscriptions DROP CONSTRAINT IF EXISTS subscriptions_status_check');

            $pg_values = implode(', ', array_map(
                fn (string $value) => "'{$value}'::character varying",
                $values
            ));

            DB::statement(
                "ALTER TABLE subscriptions ADD CONSTRAINT subscriptions_status_check CHECK ((status)::text = ANY (ARRAY[{$pg_values}]::text[]))"
            );
        }
    }
};
