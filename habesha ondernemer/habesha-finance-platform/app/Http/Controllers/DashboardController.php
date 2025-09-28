<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Expense;
use App\Models\Client;
use App\Models\Project;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        
        // Recent invoices
        $recentInvoices = Invoice::where('user_id', $user->id)
            ->with(['client', 'project'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        // Recent expenses
        $recentExpenses = Expense::where('user_id', $user->id)
            ->with('project')
            ->orderBy('expense_date', 'desc')
            ->limit(5)
            ->get();
        
        // Financial overview
        $totalInvoices = Invoice::where('user_id', $user->id)->count();
        $totalRevenue = Invoice::where('user_id', $user->id)
            ->where('status', 'paid')
            ->sum('total_amount');
        $totalExpenses = Expense::where('user_id', $user->id)->sum('amount');
        $overdueInvoices = Invoice::where('user_id', $user->id)
            ->where('status', 'sent')
            ->where('due_date', '<', now())
            ->count();
        
        // Monthly revenue (last 6 months)
        $monthlyRevenue = Invoice::where('user_id', $user->id)
            ->where('status', 'paid')
            ->where('paid_date', '>=', now()->subMonths(6))
            ->selectRaw('DATE_FORMAT(paid_date, "%Y-%m") as month, SUM(total_amount) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->get();
        
        // Monthly expenses (last 6 months)
        $monthlyExpenses = Expense::where('user_id', $user->id)
            ->where('expense_date', '>=', now()->subMonths(6))
            ->selectRaw('DATE_FORMAT(expense_date, "%Y-%m") as month, SUM(amount) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->get();
        
        return Inertia::render('Dashboard', [
            'recentInvoices' => $recentInvoices,
            'recentExpenses' => $recentExpenses,
            'stats' => [
                'totalInvoices' => $totalInvoices,
                'totalRevenue' => $totalRevenue,
                'totalExpenses' => $totalExpenses,
                'overdueInvoices' => $overdueInvoices,
                'netProfit' => $totalRevenue - $totalExpenses,
            ],
            'monthlyRevenue' => $monthlyRevenue,
            'monthlyExpenses' => $monthlyExpenses,
        ]);
    }
}
