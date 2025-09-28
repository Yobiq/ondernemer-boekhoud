<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Inertia\Inertia;
use Inertia\Response;

final class ProjectController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): Response
    {
        $projects = Project::where('user_id', $request->user()->id)
            ->with(['client'])
            ->withCount(['invoices', 'expenses'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return Inertia::render('Projects/Index', [
            'projects' => $projects,
        ]);
    }

    public function create(Request $request): Response
    {
        $clients = Client::where('user_id', $request->user()->id)
            ->orderBy('name')
            ->get();

        return Inertia::render('Projects/Create', [
            'clients' => $clients,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:active,completed,on_hold,cancelled',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'budget' => 'nullable|numeric|min:0',
            'hourly_rate' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $request->user()->projects()->create($validated);

        return redirect()->route('projects.index')
            ->with('success', 'Project succesvol aangemaakt.');
    }

    public function show(Project $project): Response
    {
        $this->authorize('view', $project);

        $project->load(['client', 'invoices.items', 'expenses']);

        // Calculate project statistics
        $totalInvoiced = $project->invoices()->where('status', 'paid')->sum('total_amount');
        $totalExpenses = $project->expenses()->sum('amount');
        $netProfit = $totalInvoiced - $totalExpenses;

        return Inertia::render('Projects/Show', [
            'project' => $project,
            'stats' => [
                'totalInvoiced' => $totalInvoiced,
                'totalExpenses' => $totalExpenses,
                'netProfit' => $netProfit,
                'invoiceCount' => $project->invoices()->count(),
                'expenseCount' => $project->expenses()->count(),
            ],
        ]);
    }

    public function edit(Project $project): Response
    {
        $this->authorize('update', $project);

        $clients = Client::where('user_id', $project->user_id)
            ->orderBy('name')
            ->get();

        return Inertia::render('Projects/Edit', [
            'project' => $project,
            'clients' => $clients,
        ]);
    }

    public function update(Request $request, Project $project): RedirectResponse
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:active,completed,on_hold,cancelled',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'budget' => 'nullable|numeric|min:0',
            'hourly_rate' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $project->update($validated);

        return redirect()->route('projects.index')
            ->with('success', 'Project succesvol bijgewerkt.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        $this->authorize('delete', $project);

        $project->delete();

        return redirect()->route('projects.index')
            ->with('success', 'Project succesvol verwijderd.');
    }
}