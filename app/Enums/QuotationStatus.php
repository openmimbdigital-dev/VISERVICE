<?php

namespace App\Enums;

enum QuotationStatus: string
{
    case Creada    = 'creada';
    case Enviada   = 'enviada';
    case Aceptada  = 'aceptada';
    case Rechazada = 'rechazada';
    case Vencida   = 'vencida';

    public function label(): string
    {
        return match ($this) {
            self::Creada    => 'Creada',
            self::Enviada   => 'Enviada',
            self::Aceptada  => 'Aceptada',
            self::Rechazada => 'Rechazada',
            self::Vencida   => 'Vencida',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Creada    => 'bg-slate-100 text-slate-600 ring-1 ring-slate-500/20',
            self::Enviada   => 'bg-blue-50 text-blue-700 ring-1 ring-blue-600/20',
            self::Aceptada  => 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20',
            self::Rechazada => 'bg-red-50 text-red-700 ring-1 ring-red-600/20',
            self::Vencida   => 'bg-orange-50 text-orange-700 ring-1 ring-orange-600/20',
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
