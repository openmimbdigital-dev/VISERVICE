<?php

namespace App\Enums;

enum BusinessBankAccountType: string
{
    case Corriente = 'corriente';
    case Ahorros   = 'ahorros';

    public function label(): string
    {
        return match ($this) {
            self::Corriente => 'Corriente',
            self::Ahorros   => 'Ahorros',
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
