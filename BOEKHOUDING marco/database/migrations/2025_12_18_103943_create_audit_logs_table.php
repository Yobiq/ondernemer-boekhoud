<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('entity_type'); // e.g., 'Document', 'Transaction'
            $table->unsignedBigInteger('entity_id');
            $table->enum('action', ['created', 'updated', 'approved', 'locked']);
            $table->jsonb('old_values')->nullable();
            $table->jsonb('new_values')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users');
            
            // CRITICAL: Only created_at, NO updated_at (immutable, append-only)
            $table->timestamp('created_at')->useCurrent();
            
            // Indexes
            $table->index(['entity_type', 'entity_id']);
            $table->index('user_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
