<?php

namespace App\Livewire\Admin\Presentation\Enrollment;

use App\Support\Presentation\AcademicDemoData;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
#[Title('Presentación — Nueva inscripción')]
class Form extends Component
{
    use WithFileUploads;

    public int $step = 1;

    public string $parent_name = '';

    public string $parent_document = '';

    public string $parent_email = '';

    public string $parent_phone = '';

    public string $child_name = '';

    public string $child_document = '';

    public string $grade = '6°';

    public string $shift = 'morning';

    public $doc_birth_certificate = null;

    public $doc_photo = null;

    public $doc_medical = null;

    public $doc_academic_record = null;

    public $doc_guardian_id = null;

    public string $payment_amount = '850000';

    public string $payment_method = 'transfer';

    public string $payment_reference = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->hasRole('superAdmin'), 403);
    }

    public function nextStep(): void
    {
        $this->validate($this->rulesForStep($this->step), $this->messages());
        $this->step = min($this->step + 1, 4);
    }

    public function previousStep(): void
    {
        $this->step = max($this->step - 1, 1);
    }

    public function save(): void
    {
        $this->validate($this->rulesForStep(4), $this->messages());

        $documents = [];
        $uploaded = 0;

        foreach (AcademicDemoData::documentTypes() as $type => $label) {
            $file = $this->{'doc_'.$type};
            $has_file = $file !== null;
            $uploaded += $has_file ? 1 : 0;
            $documents[] = [
                'type' => $type,
                'label' => $label,
                'name' => $has_file ? $file->getClientOriginalName() : null,
                'uploaded' => $has_file,
            ];
        }

        $status = 'completed';

        if ($uploaded < count(AcademicDemoData::documentTypes())) {
            $status = 'documents_pending';
        } elseif (trim($this->payment_reference) === '' && $this->payment_method !== 'cash') {
            $status = 'pending_payment';
        }

        $id = 'enr-custom-'.uniqid();

        AcademicDemoData::addEnrollment([
            'id' => $id,
            'parent_name' => trim($this->parent_name),
            'parent_document' => trim($this->parent_document),
            'parent_email' => trim($this->parent_email),
            'parent_phone' => trim($this->parent_phone),
            'child_name' => trim($this->child_name),
            'child_document' => trim($this->child_document),
            'grade' => $this->grade,
            'shift' => $this->shift,
            'documents' => $documents,
            'payment_amount' => (int) $this->payment_amount,
            'payment_method' => $this->payment_method,
            'payment_reference' => trim($this->payment_reference),
            'status' => $status,
        ]);

        $this->redirectRoute('admin.presentation.enrollment.show', $id, navigate: true);
    }

    /** @return array<string, string> */
    protected function rulesForStep(int $step): array
    {
        return match ($step) {
            1 => [
                'parent_name' => 'required|string|max:80',
                'parent_document' => 'required|string|max:20',
                'parent_email' => 'required|email|max:80',
                'parent_phone' => 'required|string|max:20',
            ],
            2 => [
                'child_name' => 'required|string|max:80',
                'child_document' => 'required|string|max:20',
                'grade' => 'required|string|max:10',
                'shift' => 'required|in:morning,afternoon',
            ],
            3 => [
                'doc_birth_certificate' => 'nullable|file|max:4096',
                'doc_photo' => 'nullable|file|max:4096',
                'doc_medical' => 'nullable|file|max:4096',
                'doc_academic_record' => 'nullable|file|max:4096',
                'doc_guardian_id' => 'nullable|file|max:4096',
            ],
            default => [
                'payment_amount' => 'required|integer|min:1',
                'payment_method' => 'required|in:transfer,cash,card',
                'payment_reference' => 'nullable|string|max:40',
            ],
        };
    }

    /** @return array<string, string> */
    protected function messages(): array
    {
        return [
            'parent_name.required' => 'El nombre del acudiente es obligatorio.',
            'parent_document.required' => 'El documento del acudiente es obligatorio.',
            'parent_email.required' => 'El correo del acudiente es obligatorio.',
            'parent_email.email' => 'Indica un correo válido.',
            'parent_phone.required' => 'El teléfono del acudiente es obligatorio.',
            'child_name.required' => 'El nombre del estudiante es obligatorio.',
            'child_document.required' => 'El documento del estudiante es obligatorio.',
            'grade.required' => 'El grado es obligatorio.',
            'shift.required' => 'La jornada es obligatoria.',
            'payment_amount.required' => 'El valor a pagar es obligatorio.',
            'payment_method.required' => 'El medio de pago es obligatorio.',
        ];
    }

    public function render()
    {
        return view('livewire.admin.presentation.enrollment.form', [
            'shifts' => AcademicDemoData::shifts(),
            'document_types' => AcademicDemoData::documentTypes(),
            'payment_methods' => AcademicDemoData::paymentMethods(),
            'grades' => ['6°', '7°', '8°', '9°', '10°', '11°'],
        ]);
    }
}
