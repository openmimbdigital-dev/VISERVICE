<?php

namespace App\Enums;

enum ElectronicInvoiceStatus: string
{
    case Pending = 'pending';
    case Sent = 'sent';
    case Accepted = 'accepted';
    case Rejected = 'rejected';
    case Error = 'error';

    public function label(): string
    {
        return match ($this) {
            self::Pending  => 'Pendiente de envío',
            self::Sent     => 'Enviada a la DIAN',
            self::Accepted => 'Validada por la DIAN',
            self::Rejected => 'Rechazada por la DIAN',
            self::Error    => 'Error de emisión',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Pending  => 'bg-slate-100 text-slate-600 ring-1 ring-slate-500/20',
            self::Sent     => 'bg-blue-50 text-blue-700 ring-1 ring-blue-600/20',
            self::Accepted => 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20',
            self::Rejected => 'bg-red-50 text-red-700 ring-1 ring-red-600/20',
            self::Error    => 'bg-amber-50 text-amber-700 ring-1 ring-amber-600/20',
        };
    }

    /** El documento ya fue aceptado por la DIAN y no admite reenvío. */
    public function isFinal(): bool
    {
        return $this === self::Accepted;
    }

    /** Puede intentarse (o reintentarse) la emisión conservando el consecutivo. */
    public function canBeSent(): bool
    {
        return $this === self::Pending || $this === self::Error || $this === self::Rejected;
    }

    /** Ya tiene transacción en el proveedor: se puede consultar estado y descargar. */
    public function hasTransaction(): bool
    {
        return $this === self::Sent || $this === self::Accepted || $this === self::Rejected;
    }

    /**
     * Traduce la cronología de texto que devuelve el proveedor
     * ("Recibida, Validada, Enviado a DIAN, Validado por DIAN") al estado local.
     */
    public static function fromProviderTimeline(string $timeline, self $current): self
    {
        $normalized = mb_strtolower($timeline);

        if (trim($normalized) === '') {
            return $current;
        }

        if (str_contains($normalized, 'rechaz')) {
            return self::Rejected;
        }

        if (str_contains($normalized, 'validado por dian') || str_contains($normalized, 'aceptad')) {
            return self::Accepted;
        }

        return self::Sent;
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
