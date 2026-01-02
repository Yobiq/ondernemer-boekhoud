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
        Schema::create('btw_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('period'); // e.g., "2024-Q1"
            $table->enum('status', ['draft', 'reviewed', 'client_approved', 'submitted', 'locked'])
                ->default('draft');
            $table->jsonb('totals')->nullable(); // Dutch BTW rubrieken (1a, 1b, 1c, 2a, 3a, 3b, 4a, 4b, 5b)
            $table->timestamp('locked_at')->nullable(); // When report was locked
            $table->timestamps();
            
            // Indexes
            $table->index('client_id');
            $table->index('period');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('btw_reports');
    }
};
