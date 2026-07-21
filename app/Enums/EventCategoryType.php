<?php

namespace App\Enums;

enum EventCategoryType: string
{
    case Periodico = 'periodico';
    case Eventual  = 'eventual';

    public function label(): string
    {
        return match ($this) {
            self::Periodico => 'Periódico',
            self::Eventual  => 'Eventual',
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
