<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Inertia\Inertia;
use Inertia\Response;

final class ExpenseController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): Response
    {
        $expenses = Expense::where('user_id', $request->user()->id)
            ->with('project')
            ->orderBy('expense_date', 'desc')
            ->paginate(15);

        return Inertia::render('Expenses/Index', [
            'expenses' => $expenses,
        ]);
    }

    public function create(Request $request): Response
    {
        $projects = Project::where('user_id', $request->user()->id)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return Inertia::render('Expenses/Create', [
            'projects' => $projects,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'project_id' => 'nullable|exists:projects,id',
            'description' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'amount' => 'required|numeric|min:0.01',
            'expense_date' => 'required|date',
            'receipt_path' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'is_billable' => 'boolean',
        ]);

        $request->user()->expenses()->create($validated);

        return redirect()->route('expenses.index')
            ->with('success', 'Uitgave succesvol toegevoegd.');
    }

    public function show(Expense $expense): Response
    {
        $this->authorize('view', $expense);

        $expense->load('project');

        return Inertia::render('Expenses/Show', [
            'expense' => $expense,
        ]);
    }

    public function edit(Expense $expense): Response
    {
        $this->authorize('update', $expense);

        $projects = Project::where('user_id', $expense->user_id)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return Inertia::render('Expenses/Edit', [
            'expense' => $expense,
            'projects' => $projects,
        ]);
    }

    public function update(Request $request, Expense $expense): RedirectResponse
    {
        $this->authorize('update', $expense);

        $validated = $request->validate([
            'project_id' => 'nullable|exists:projects,id',
            'description' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'amount' => 'required|numeric|min:0.01',
            'expense_date' => 'required|date',
            'receipt_path' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'is_billable' => 'boolean',
        ]);

        $expense->update($validated);

        return redirect()->route('expenses.index')
            ->with('success', 'Uitgave succesvol bijgewerkt.');
    }

    public function destroy(Expense $expense): RedirectResponse
    {
        $this->authorize('delete', $expense);

        $expense->delete();

        return redirect()->route('expenses.index')
            ->with('success', 'Uitgave succesvol verwijderd.');
    }
}
