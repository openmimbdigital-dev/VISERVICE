<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Status extends Model
{
    protected $fillable = [
        'name',
        'label',
        'active',
        'type',
    ];

    /** @var list<string> */
    public const USAGE_TABLES = [
        'quotations',
        'work_orders',
        'remissions',
        'work_order_payments',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'type'   => 'array',
        ];
    }

    public function scopeForModule(Builder $query, string $module): Builder
    {
        return $query
            ->where('active', true)
            ->whereJsonContains('type', $module);
    }

    /** @return array<string, string> */
    public static function optionsForModule(string $module): array
    {
        return static::query()
            ->forModule($module)
            ->orderBy('id')
            ->pluck('label', 'name')
            ->all();
    }

    public function isInUse(): bool
    {
        return static::isNameInUse($this->name);
    }

    public static function isNameInUse(string $name): bool
    {
        foreach (static::USAGE_TABLES as $table) {
            if (! DB::getSchemaBuilder()->hasTable($table)) {
                continue;
            }

            if (DB::table($table)->where('status', $name)->exists()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Nombres de estado referenciados en tablas del sistema.
     *
     * @return list<string>
     */
    public static function namesInUse(): array
    {
        $names = [];

        foreach (static::USAGE_TABLES as $table) {
            if (! DB::getSchemaBuilder()->hasTable($table)) {
                continue;
            }

            foreach (DB::table($table)->distinct()->pluck('status') as $status_name) {
                if (is_string($status_name) && $status_name !== '') {
                    $names[$status_name] = true;
                }
            }
        }

        return array_keys($names);
    }
}
