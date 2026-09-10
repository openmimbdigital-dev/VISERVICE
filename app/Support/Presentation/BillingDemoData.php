<?php

namespace App\Support\Presentation;

class BillingDemoData
{
    public const SESSION_INVOICES_KEY = 'presentation.billing.invoices';

    public const SESSION_CONCEPTS_KEY = 'presentation.billing.concepts';

    public const SESSION_PAYMENTS_KEY = 'presentation.billing.payments';

    /** @return array<string, string> */
    public static function invoiceStatuses(): array
    {
        return [
            'draft' => 'Borrador',
            'issued' => 'Emitida',
            'paid' => 'Pagada',
            'overdue' => 'Vencida',
        ];
    }

    /** @return list<array<string, mixed>> */
    public static function defaultConcepts(): array
    {
        return [
            ['id' => 'con-pension', 'name' => 'Pensión', 'amount' => 850000, 'active' => true],
            ['id' => 'con-transporte', 'name' => 'Transporte', 'amount' => 180000, 'active' => true],
            ['id' => 'con-alimentacion', 'name' => 'Alimentación', 'amount' => 220000, 'active' => true],
            ['id' => 'con-materiales', 'name' => 'Materiales', 'amount' => 95000, 'active' => true],
        ];
    }

    /** @return list<array<string, mixed>> */
    public static function defaultInvoices(): array
    {
        return [
            ['id' => 'inv-1', 'number' => 'FAC-2026-001', 'student' => 'Martina Mejía', 'guardian' => 'Carolina Mejía', 'concept' => 'Pensión', 'amount' => 850000, 'issued_at' => '2026-09-01', 'due_date' => '2026-09-10', 'status' => 'paid'],
            ['id' => 'inv-2', 'number' => 'FAC-2026-002', 'student' => 'Liam Castaño', 'guardian' => 'Andrés Castaño', 'concept' => 'Pensión', 'amount' => 850000, 'issued_at' => '2026-09-01', 'due_date' => '2026-09-10', 'status' => 'issued'],
            ['id' => 'inv-3', 'number' => 'FAC-2026-003', 'student' => 'Emma Restrepo', 'guardian' => 'Paola Restrepo', 'concept' => 'Transporte', 'amount' => 180000, 'issued_at' => '2026-09-02', 'due_date' => '2026-09-12', 'status' => 'overdue'],
            ['id' => 'inv-4', 'number' => 'FAC-2026-004', 'student' => 'Santiago Morales', 'guardian' => 'Familia Morales', 'concept' => 'Alimentación', 'amount' => 220000, 'issued_at' => '2026-09-05', 'due_date' => '2026-09-20', 'status' => 'draft'],
        ];
    }

    /** @return list<array<string, mixed>> */
    public static function defaultPayments(): array
    {
        return [
            ['id' => 'pay-1', 'invoice_number' => 'FAC-2026-001', 'student' => 'Martina Mejía', 'amount' => 850000, 'method' => 'Transferencia', 'paid_at' => '2026-09-08', 'reference' => 'TRX-90821'],
            ['id' => 'pay-2', 'invoice_number' => 'FAC-2026-003', 'student' => 'Emma Restrepo', 'amount' => 90000, 'method' => 'Efectivo', 'paid_at' => '2026-09-09', 'reference' => 'CAJA-112'],
        ];
    }

    /** @return list<array<string, mixed>> */
    public static function concepts(): array
    {
        return self::merge(self::defaultConcepts(), self::SESSION_CONCEPTS_KEY);
    }

    /** @return list<array<string, mixed>> */
    public static function invoices(): array
    {
        return array_map(fn (array $row) => self::decorateInvoice($row), self::merge(self::defaultInvoices(), self::SESSION_INVOICES_KEY));
    }

    /** @return list<array<string, mixed>> */
    public static function payments(): array
    {
        return self::merge(self::defaultPayments(), self::SESSION_PAYMENTS_KEY);
    }

    /** @return array<string, mixed>|null */
    public static function invoice(string $id): ?array
    {
        foreach (self::invoices() as $invoice) {
            if ($invoice['id'] === $id) {
                return $invoice;
            }
        }

        return null;
    }

    /** @param array<string, mixed> $invoice */
    public static function addInvoice(array $invoice): void
    {
        self::push(self::SESSION_INVOICES_KEY, $invoice);
    }

    /** @param array<string, mixed> $concept */
    public static function addConcept(array $concept): void
    {
        self::push(self::SESSION_CONCEPTS_KEY, $concept);
    }

    /** @param array<string, mixed> $payment */
    public static function addPayment(array $payment): void
    {
        self::push(self::SESSION_PAYMENTS_KEY, $payment);
    }

    /** @param array<string, mixed> $invoice */
    public static function decorateInvoice(array $invoice): array
    {
        $invoice['status_label'] = self::invoiceStatuses()[$invoice['status']] ?? $invoice['status'];

        return $invoice;
    }

    public static function nextInvoiceNumber(): string
    {
        return 'FAC-2026-'.str_pad((string) (count(self::invoices()) + 1), 3, '0', STR_PAD_LEFT);
    }

    /** @param list<array<string, mixed>> $defaults */
    private static function merge(array $defaults, string $key): array
    {
        $created = session($key, []);

        return array_values(array_merge($defaults, is_array($created) ? $created : []));
    }

    /** @param array<string, mixed> $item */
    private static function push(string $key, array $item): void
    {
        $created = session($key, []);
        if (! is_array($created)) {
            $created = [];
        }
        $created[] = $item;
        session([$key => $created]);
    }
}
