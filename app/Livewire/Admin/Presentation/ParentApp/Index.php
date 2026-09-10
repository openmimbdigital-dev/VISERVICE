<?php

namespace App\Livewire\Admin\Presentation\ParentApp;

use App\Support\Presentation\AcademicDemoData;
use App\Support\Presentation\BillingDemoData;
use App\Support\Presentation\EventDemoData;
use App\Support\Presentation\ParentAppDemoData;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Presentación — App de padres')]
class Index extends Component
{
    public string $screen = 'home';

    public string $subject_id = '';

    public string $course_id = '';

    public string $invoice_id = '';

    public string $event_id = '';

    public string $pay_method = 'Transferencia';

    public string $flash = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->hasRole('superAdmin'), 403);
        $this->course_id = ParentAppDemoData::child()['course_id'];
    }

    public function go(string $screen, string $id = ''): void
    {
        $this->flash = '';
        $this->screen = $screen;

        if ($screen === 'subject') {
            abort_unless(AcademicDemoData::subject($id) !== null, 404);
            $this->subject_id = $id;
        }

        if ($screen === 'course') {
            abort_unless(AcademicDemoData::course($id) !== null, 404);
            $this->course_id = $id;
        }

        if ($screen === 'pay') {
            abort_unless(BillingDemoData::invoice($id) !== null, 404);
            $this->invoice_id = $id;
            $this->pay_method = 'Transferencia';
        }

        if ($screen === 'event') {
            abort_unless(EventDemoData::event($id) !== null, 404);
            $this->event_id = $id;
        }
    }

    public function confirmPay(): void
    {
        $invoice = BillingDemoData::invoice($this->invoice_id);
        abort_unless($invoice !== null, 404);
        abort_unless(in_array($invoice['status'], ['issued', 'overdue'], true), 403);

        $this->validate([
            'pay_method' => 'required|string|max:40',
        ]);

        BillingDemoData::addPayment([
            'id' => 'pay-parent-'.uniqid(),
            'invoice_number' => $invoice['number'],
            'student' => $invoice['student'],
            'amount' => (int) $invoice['amount'],
            'method' => $this->pay_method,
            'paid_at' => now()->toDateString(),
            'reference' => 'APP-'.random_int(1000, 9999),
        ]);
        BillingDemoData::setInvoiceStatus($this->invoice_id, 'paid');

        $this->flash = 'Pago registrado';
        $this->screen = 'payments';
    }

    public function render()
    {
        $child = ParentAppDemoData::child();
        $tab = match ($this->screen) {
            'subject' => 'classroom',
            'course' => 'courses',
            'pay' => 'payments',
            'event' => 'events',
            'enrollment' => 'home',
            default => $this->screen,
        };

        return view('livewire.admin.presentation.parent-app.index', [
            'guardian' => ParentAppDemoData::guardian(),
            'child' => $child,
            'subjects' => ParentAppDemoData::subjects(),
            'subject_record' => $this->subject_id !== '' ? AcademicDemoData::subject($this->subject_id) : null,
            'subject_activities' => $this->subject_id !== '' ? AcademicDemoData::activitiesForSubject($this->subject_id) : [],
            'course_record' => AcademicDemoData::course($this->course_id ?: $child['course_id']),
            'enrollment' => AcademicDemoData::enrollment($child['enrollment_id']),
            'invoices' => ParentAppDemoData::invoices(),
            'invoice_record' => $this->invoice_id !== '' ? BillingDemoData::invoice($this->invoice_id) : null,
            'events' => ParentAppDemoData::events(),
            'event_record' => $this->event_id !== '' ? EventDemoData::event($this->event_id) : null,
            'tab' => $tab,
            'header_title' => $this->headerTitle(),
            'show_back' => ! in_array($this->screen, ['home', 'classroom', 'courses', 'payments', 'events'], true),
        ]);
    }

    private function headerTitle(): string
    {
        return match ($this->screen) {
            'home' => 'Colegio',
            'classroom', 'subject' => 'Mi aula',
            'courses', 'course' => 'Cursos',
            'enrollment' => 'Inscripción',
            'payments', 'pay' => 'Pagos',
            'events', 'event' => 'Eventos',
            default => 'Colegio',
        };
    }
}
