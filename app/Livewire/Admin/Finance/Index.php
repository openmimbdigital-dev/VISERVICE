<?php

namespace App\Livewire\Admin\Finance;

use App\Models\BankAccount;
use App\Models\Business;
use App\Models\Subscription;
use App\Models\SubscriptionInvoice;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Panel Financiero')]
class Index extends Component
{
    public function render()
    {
        // ── KPIs ──────────────────────────────────────────────────────────
        $totalCobrado     = (float) SubscriptionInvoice::where('status', 'paid')->sum('amount');
        $estesMes         = (float) SubscriptionInvoice::where('status', 'paid')
                                ->whereMonth('paid_at', now()->month)
                                ->whereYear('paid_at', now()->year)
                                ->sum('amount');
        $mesPasado        = (float) SubscriptionInvoice::where('status', 'paid')
                                ->whereMonth('paid_at', now()->subMonth()->month)
                                ->whereYear('paid_at', now()->subMonth()->year)
                                ->sum('amount');
        $pendiente        = (float) SubscriptionInvoice::where('status', 'pending')->sum('amount');
        $activeSubs       = Subscription::whereIn('status', ['active', 'trial'])->count();
        $negociosActivos  = Business::whereHas('subscriptions', fn ($q) => $q->whereIn('status', ['active', 'trial']))->count();
        $ticketPromedio   = (float) (SubscriptionInvoice::where('status', 'paid')->avg('amount') ?? 0);
        $esteAnio         = (float) SubscriptionInvoice::where('status', 'paid')
                                ->whereYear('paid_at', now()->year)
                                ->sum('amount');
        $vencido          = (float) SubscriptionInvoice::where('status', 'pending')
                                ->where('due_date', '<', now())
                                ->sum('amount');
        $mesVariacion     = $mesPasado > 0
                                ? round((($estesMes - $mesPasado) / $mesPasado) * 100, 1)
                                : ($estesMes > 0 ? 100 : 0);

        // ── Gráfica de ingresos — últimos 12 meses ────────────────────────
        $paidByMonth = SubscriptionInvoice::where('status', 'paid')
            ->where('paid_at', '>=', now()->subMonths(11)->startOfMonth())
            ->selectRaw("DATE_FORMAT(paid_at, '%Y-%m') as ym, SUM(amount) as total")
            ->groupBy('ym')
            ->pluck('total', 'ym');

        $chartMonths  = [];
        $chartRevenue = [];
        for ($i = 11; $i >= 0; $i--) {
            $d = now()->subMonths($i);
            $chartMonths[]  = ucfirst($d->locale('es')->isoFormat('MMM YY'));
            $chartRevenue[] = (float) ($paidByMonth[$d->format('Y-m')] ?? 0);
        }

        // ── Distribución por plan ─────────────────────────────────────────
        $planRows   = Subscription::whereIn('status', ['active', 'trial'])
            ->join('subscription_plans', 'subscriptions.subscription_plan_id', '=', 'subscription_plans.id')
            ->selectRaw('subscription_plans.name as name, COUNT(subscriptions.id) as total')
            ->groupBy('subscription_plans.id', 'subscription_plans.name')
            ->get();
        $planLabels = $planRows->pluck('name')->toArray();
        $planValues = $planRows->pluck('total')->map(fn ($v) => (int) $v)->toArray();

        // ── Estado de suscripciones ───────────────────────────────────────
        $statusRows = Subscription::selectRaw('status, COUNT(*) as total')->groupBy('status')->get();
        $statusMap  = [
            'pending'   => ['label' => 'Pago pendiente', 'color' => '#f59e0b'],
            'trial'     => ['label' => 'Prueba',         'color' => '#3b82f6'],
            'active'    => ['label' => 'Activa',         'color' => '#10b981'],
            'past_due'  => ['label' => 'Vencida',        'color' => '#f97316'],
            'cancelled' => ['label' => 'Cancelada',      'color' => '#6b7280'],
            'expired'   => ['label' => 'Expirada',       'color' => '#ef4444'],
        ];
        $statusLabels = $statusRows->map(fn ($r) => $statusMap[$r->status]['label'] ?? $r->status)->toArray();
        $statusValues = $statusRows->pluck('total')->map(fn ($v) => (int) $v)->toArray();
        $statusColors = $statusRows->map(fn ($r) => $statusMap[$r->status]['color'] ?? '#94a3b8')->toArray();

        // ── Distribución por ciclo de facturación ─────────────────────────
        $cycleRows  = Subscription::whereIn('status', ['active', 'trial'])
            ->selectRaw('billing_cycle, COUNT(*) as total, SUM(total_price) as revenue')
            ->groupBy('billing_cycle')
            ->get();
        $cycleMap   = ['monthly' => 'Mensual', 'quarterly' => 'Trimestral', 'semiannual' => 'Semestral', 'annual' => 'Anual'];
        $cycleLabels   = $cycleRows->map(fn ($r) => $cycleMap[$r->billing_cycle] ?? $r->billing_cycle)->toArray();
        $cycleCounts   = $cycleRows->pluck('total')->map(fn ($v) => (int) $v)->toArray();
        $cycleRevenue  = $cycleRows->pluck('revenue')->map(fn ($v) => (float) $v)->toArray();

        // ── Distribución por cuenta bancaria y efectivo ───────────────────
        $byAccount = SubscriptionInvoice::where('status', 'paid')
            ->whereNotNull('bank_account_id')
            ->selectRaw('bank_account_id, SUM(amount) as total, COUNT(*) as invoices')
            ->groupBy('bank_account_id')
            ->with('bankAccount.bank')
            ->orderByDesc('total')
            ->get();

        $cashTotal  = (float) SubscriptionInvoice::where('status', 'paid')->whereNull('bank_account_id')->sum('amount');
        $cashCount  = (int)   SubscriptionInvoice::where('status', 'paid')->whereNull('bank_account_id')->count();

        // ── Tablas ────────────────────────────────────────────────────────
        $recentPaid      = SubscriptionInvoice::with(['business', 'subscription.plan'])
            ->where('status', 'paid')
            ->orderByDesc('paid_at')
            ->limit(8)
            ->get();

        $pendingInvoices = SubscriptionInvoice::with(['business', 'subscription.plan'])
            ->where('status', 'pending')
            ->orderBy('due_date')
            ->limit(10)
            ->get();

        return view('livewire.admin.finance.index', compact(
            'totalCobrado', 'estesMes', 'mesPasado', 'mesVariacion',
            'pendiente', 'activeSubs', 'negociosActivos',
            'ticketPromedio', 'esteAnio', 'vencido',
            'chartMonths', 'chartRevenue',
            'planLabels', 'planValues',
            'statusLabels', 'statusValues', 'statusColors',
            'cycleLabels', 'cycleCounts', 'cycleRevenue',
            'byAccount', 'cashTotal', 'cashCount',
            'recentPaid', 'pendingInvoices'
        ));
    }
}
