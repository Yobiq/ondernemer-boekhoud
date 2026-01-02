<?php

namespace App\Services;

use App\Models\Document;
use App\Models\Task;
use App\Jobs\ProcessDocumentOcrJob;
use Illuminate\Support\Facades\Log;

class TaskService
{
    /**
     * Create a task for a client regarding a document
     */
    public function createTask(Document $document, string $type, string $description): Task
    {
        // Update document status
        $document->update(['status' => 'task_opened']);
        
        $task = Task::create([
            'client_id' => $document->client_id,
            'document_id' => $document->id,
            'type' => $type,
            'description' => $description,
            'status' => 'open',
        ]);
        
        Log::info('Task created', [
            'task_id' => $task->id,
            'document_id' => $document->id,
            'type' => $type,
        ]);
        
        return $task;
    }
    
    /**
     * Resolve a task when client uploads response
     */
    public function resolveTask(Task $task, ?Document $responseDocument = null): void
    {
        $task->update(['status' => 'resolved']);
        
        // If there's an original document and client uploaded response,
        // reprocess the original document
        if ($task->document_id && $responseDocument) {
            $originalDocument = $task->document;
            
            if ($originalDocument) {
                Log::info('Task resolved with new document, reprocessing original', [
                    'task_id' => $task->id,
                    'original_document_id' => $originalDocument->id,
                    'response_document_id' => $responseDocument->id,
                ]);
                
                // Reset original document to pending and reprocess
                $originalDocument->update(['status' => 'pending']);
                ProcessDocumentOcrJob::dispatch($originalDocument);
            }
        }
        
        Log::info('Task resolved', ['task_id' => $task->id]);
    }
    
    /**
     * Get open tasks for a client
     */
    public function getOpenTasksForClient(int $clientId)
    {
        return Task::where('client_id', $clientId)
            ->where('status', 'open')
            ->with('document')
            ->orderBy('created_at', 'desc')
            ->get();
    }
    
    /**
     * Get task type descriptions in Dutch
     */
    public function getTypeDescription(string $type): string
    {
        return match($type) {
            'missing_document' => 'Ontbrekend document',
            'unreadable' => 'Document onleesbaar',
            'clarification' => 'Verduidelijking nodig',
            default => 'Onbekend',
        };
    }
}

