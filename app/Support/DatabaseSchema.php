<?php

namespace App\Support;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Operaciones de esquema compatibles con MySQL y PostgreSQL.
 * Usa Schema/Blueprint cuando sea posible; solo aquí va SQL crudo por driver.
 */
class DatabaseSchema
{
    public static function driver(): string
    {
        return Schema::getConnection()->getDriverName();
    }

    public static function isMysql(): bool
    {
        return in_array(static::driver(), ['mysql', 'mariadb'], true);
    }

    public static function isPgsql(): bool
    {
        return static::driver() === 'pgsql';
    }

    public static function hasIndex(string $table, string|array $index, ?string $type = null): bool
    {
        return Schema::hasIndex($table, $index, $type);
    }

    /**
     * @param  list<string>  $columns
     */
    public static function addIndexIfMissing(string $table, string $index_name, array $columns): void
    {
        if (static::hasIndex($table, $index_name)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($index_name, $columns) {
            $blueprint->index($columns, $index_name);
        });
    }

    /**
     * Actualiza los valores permitidos de una columna creada con $table->enum().
     * MySQL: MODIFY ENUM. PostgreSQL: recrea el CHECK (nombre {table}_{column}_check).
     *
     * @param  list<string>  $values
     */
    public static function updateEnumColumn(
        string $table,
        string $column,
        array $values,
        string $default = 'active'
    ): void {
        $quoted = implode(',', array_map(
            static fn (string $value) => "'".str_replace("'", "''", $value)."'",
            $values
        ));

        if (static::isMysql()) {
            DB::statement(
                "ALTER TABLE {$table} MODIFY COLUMN {$column} ENUM({$quoted}) DEFAULT '".str_replace("'", "''", $default)."'"
            );

            return;
        }

        if (static::isPgsql()) {
            $constraint = "{$table}_{$column}_check";

            DB::statement("ALTER TABLE {$table} DROP CONSTRAINT IF EXISTS {$constraint}");

            $pg_values = implode(', ', array_map(
                static fn (string $value) => "'".str_replace("'", "''", $value)."'::character varying",
                $values
            ));

            DB::statement(
                "ALTER TABLE {$table} ADD CONSTRAINT {$constraint} CHECK (({$column})::text = ANY (ARRAY[{$pg_values}]::text[]))"
            );

            return;
        }

        throw new \RuntimeException('Driver de base de datos no soportado para updateEnumColumn: '.static::driver());
    }
}
