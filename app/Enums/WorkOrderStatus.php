<?php

namespace App\Enums;

enum WorkOrderStatus: string
{
    case Created = 'created';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Created => 'Creada',
            self::InProgress => 'En proceso',
            self::Completed => 'Finalizada',
            self::Cancelled => 'Cancelada',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Created => 'bg-blue-50 text-blue-700 ring-1 ring-blue-600/20',
            self::InProgress => 'bg-yellow-50 text-yellow-700 ring-1 ring-yellow-600/20',
            self::Completed => 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20',
            self::Cancelled => 'bg-red-50 text-red-700 ring-1 ring-red-600/20',
        };
    }

    public function isOpen(): bool
    {
        return $this === self::Created || $this === self::InProgress;
    }

    public function isTerminal(): bool
    {
        return $this === self::Completed || $this === self::Cancelled;
    }

    public function allowsAssociatedDocuments(): bool
    {
        return $this !== self::Cancelled;
    }

    public function canReceiveRemission(): bool
    {
        return $this !== self::Cancelled;
    }

    /** @return list<string> */
    public static function openValues(): array
    {
        return [self::Created->value, self::InProgress->value];
    }

    /** @return list<string> */
    public static function remissionEligibleValues(): array
    {
        return [self::Created->value, self::InProgress->value, self::Completed->value];
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
