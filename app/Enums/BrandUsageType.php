<?php

namespace App\Enums;

enum BrandUsageType: string
{
    case Equipment = 'equipment';
    case Products  = 'products';

    public function label(): string
    {
        return match ($this) {
            self::Equipment => 'Equipos',
            self::Products  => 'Productos / servicios',
        };
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        $options = [];

        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }
}
