<?php

namespace App\Enums;

enum QuotationStatus: string
{
    case Created = 'created';
    case Sent = 'sent';
    case Accepted = 'accepted';
    case Rejected = 'rejected';
    case Expired = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::Created => 'Creada',
            self::Sent => 'Enviada',
            self::Accepted => 'Aceptada',
            self::Rejected => 'Rechazada',
            self::Expired => 'Vencida',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Created => 'bg-slate-100 text-slate-600 ring-1 ring-slate-500/20',
            self::Sent => 'bg-blue-50 text-blue-700 ring-1 ring-blue-600/20',
            self::Accepted => 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20',
            self::Rejected => 'bg-red-50 text-red-700 ring-1 ring-red-600/20',
            self::Expired => 'bg-orange-50 text-orange-700 ring-1 ring-orange-600/20',
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
