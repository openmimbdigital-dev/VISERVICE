<?php

namespace App\Livewire\Admin\Presentation\Billing;

use App\Support\Presentation\AcademicDemoData;
use App\Support\Presentation\BillingDemoData;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Presentación — Nueva factura')]
class InvoiceForm extends Component
{
    public string $family_id = '';

    public string $student = '';

    public string $guardian = '';

    public string $concept = '';

    public string $amount = '';

    public string $due_date = '';

    public string $status = 'issued';

    public function mount(): void
    {
        abort_unless(auth()->user()?->hasRole('superAdmin'), 403);
        $concepts = BillingDemoData::concepts();
        $this->concept = $concepts[0]['name'] ?? '';
        $this->amount = (string) ($concepts[0]['amount'] ?? 0);
        $this->due_date = now()->addDays(10)->format('Y-m-d');

        $enrollment = AcademicDemoData::enrollments()[0] ?? null;
        if ($enrollment) {
            $this->family_id = $enrollment['id'];
            $this->student = $enrollment['child_name'];
            $this->guardian = $enrollment['parent_name'];
        }
    }

    public function updatedFamilyId(string $value): void
    {
        $enrollment = AcademicDemoData::enrollment($value);
        if ($enrollment === null) {
            return;
        }

        $this->student = $enrollment['child_name'];
        $this->guardian = $enrollment['parent_name'];
    }

    public function updatedConcept(string $value): void
    {
        foreach (BillingDemoData::concepts() as $concept) {
            if ($concept['name'] === $value) {
                $this->amount = (string) $concept['amount'];
                break;
            }
        }
    }

    public function save(): void
    {
        $this->validate([
            'student' => 'required|string|max:80',
            'guardian' => 'required|string|max:80',
            'concept' => 'required|string|max:80',
            'amount' => 'required|integer|min:1',
            'due_date' => 'required|date',
            'status' => 'required|in:draft,issued,paid,overdue',
        ], [
            'student.required' => 'El estudiante es obligatorio.',
            'guardian.required' => 'El acudiente es obligatorio.',
            'concept.required' => 'El concepto es obligatorio.',
            'amount.required' => 'El valor es obligatorio.',
            'due_date.required' => 'La fecha de vencimiento es obligatoria.',
        ]);

        $id = 'inv-custom-'.uniqid();

        BillingDemoData::addInvoice([
            'id' => $id,
            'number' => BillingDemoData::nextInvoiceNumber(),
            'student' => trim($this->student),
            'guardian' => trim($this->guardian),
            'concept' => $this->concept,
            'amount' => (int) $this->amount,
            'issued_at' => now()->toDateString(),
            'due_date' => $this->due_date,
            'status' => $this->status,
        ]);

        $this->redirectRoute('admin.presentation.billing.invoices.show', $id, navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.presentation.billing.invoice-form', [
            'concepts' => BillingDemoData::concepts(),
            'statuses' => BillingDemoData::invoiceStatuses(),
            'families' => AcademicDemoData::enrollments(),
        ]);
    }
}
