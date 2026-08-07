<?php

namespace App\Livewire\Admin\Workshop\AdvancePayments;

use App\Models\WorkOrder;
use App\Models\WorkOrderPayment;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Gestión de anticipo — Taller')]
class Index extends Component
{
    public function mount(): void
    {
        abort_unless(auth()->user()?->can('workshop.advance-payments.view'), 403);
    }

    public function render()
    {
        $payments = WorkOrderPayment::query()->forAuthUser();

        $paid_subquery = DB::table('work_order_payments')
            ->select('work_order_id', DB::raw('SUM(amount) as paid_sum'))
            ->whereNull('deleted_at')
            ->where('status', 'confirmed')
            ->groupBy('work_order_id');

        $pending_balance = (float) (WorkOrder::query()
            ->forAuthUser()
            ->where('work_orders.advance_amount', '>', 0)
            ->leftJoinSub($paid_subquery, 'advance_paid', 'advance_paid.work_order_id', '=', 'work_orders.id')
            ->selectRaw('COALESCE(SUM(GREATEST(work_orders.advance_amount - COALESCE(advance_paid.paid_sum, 0), 0)), 0) as pending_balance')
            ->value('pending_balance') ?? 0);

        $stats = [
            'with_advance' => WorkOrder::query()->forAuthUser()->where('advance_amount', '>', 0)->count(),
            'total_confirmed' => (clone $payments)->where('status', 'confirmed')->sum('amount'),
            'pending_balance' => $pending_balance,
        ];

        return view('livewire.admin.workshop.advance-payments.index', compact('stats'));
    }
}
