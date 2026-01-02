<?php

namespace App\Filament\Client\Widgets;

use App\Models\Document;
use App\Models\Task;
use App\Models\AuditLog;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;

class StatusUpdatesWidget extends Widget
{
    protected static string $view = 'filament.client.widgets.status-updates-widget';
    protected int | string | array $columnSpan = ['default' => 12, 'md' => 6, 'lg' => 4];
    protected static ?int $sort = 6;
    
    // Poll every 30 seconds for real-time updates
    protected $poll = '30s';
    
    public static function canView(): bool
    {
        // Disabled - widget causes large arrow icon on dashboard
            return false;
    }
    
    protected function getViewData(): array
    {
        if (!Auth::check()) {
            return ['updates' => [], 'has_data' => false];
        }
        
        $clientId = Auth::user()->client_id ?? null;
        
        if (!$clientId) {
            return ['updates' => [], 'has_data' => false];
        }
        
        $updates = $this->getRecentUpdates($clientId);
        
        return [
            'updates' => $updates,
            'has_data' => $updates->isNotEmpty(),
        ];
    }
    
    protected function getRecentUpdates(int $clientId): Collection
    {
        $updates = collect();
        
        // Get recently updated documents (last 24 hours)
        $documents = Document::where('client_id', $clientId)
            ->where('updated_at', '>=', now()->subDay())
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get();
        
        foreach ($documents as $doc) {
            $updates->push([
                'type' => 'document',
                'icon' => $this->getStatusIcon($doc->status),
                'color' => $this->getStatusColor($doc->status),
                'title' => $this->getStatusTitle($doc->status),
                'description' => $doc->original_filename,
                'time' => $doc->updated_at,
                'time_human' => $doc->updated_at->diffForHumans(),
                'document_id' => $doc->id,
            ]);
        }
        
        // Get recent tasks (last 24 hours)
        $tasks = Task::where('client_id', $clientId)
            ->where('created_at', '>=', now()->subDay())
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        foreach ($tasks as $task) {
            $updates->push([
                'type' => 'task',
                'icon' => $task->status === 'open' ? '⚠️' : '✅',
                'color' => $task->status === 'open' ? 'warning' : 'success',
                'title' => $task->status === 'open' ? 'Nieuwe taak toegewezen' : 'Taak voltooid',
                'description' => $task->description,
                'time' => $task->created_at,
                'time_human' => $task->created_at->diffForHumans(),
                'document_id' => $task->document_id,
            ]);
        }
        
        // Sort all updates by time
        return $updates->sortByDesc('time')->take(10);
    }
    
    protected function getStatusIcon(string $status): string
    {
        return match($status) {
            'pending' => '⏳',
            'ocr_processing' => '🔄',
            'review_required' => '👀',
            'approved' => '✅',
            'archived' => '📦',
            'task_opened' => '⚠️',
            default => '📄',
        };
    }
    
    protected function getStatusColor(string $status): string
    {
        return match($status) {
            'pending' => 'gray',
            'ocr_processing' => 'info',
            'review_required' => 'warning',
            'approved' => 'success',
            'archived' => 'danger',
            'task_opened' => 'warning',
            default => 'gray',
        };
    }
    
    protected function getStatusTitle(string $status): string
    {
        return match($status) {
            'pending' => 'Document in wachtrij',
            'ocr_processing' => 'Document wordt verwerkt',
            'review_required' => 'Document in beoordeling',
            'approved' => 'Document goedgekeurd',
            'archived' => 'Document gearchiveerd',
            'task_opened' => 'Actie vereist',
            default => 'Status gewijzigd',
        };
    }
}

