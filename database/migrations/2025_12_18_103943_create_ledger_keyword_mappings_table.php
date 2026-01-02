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
        Schema::create('ledger_keyword_mappings', function (Blueprint $table) {
            $table->id();
            $table->string('keyword'); // e.g., "telefoon", "kantoor", "reizen"
            $table->foreignId('ledger_account_id')->constrained('ledger_accounts')->cascadeOnDelete();
            $table->integer('priority')->default(0); // Higher priority = preferred match
            $table->timestamps();
            
            // Indexes
            $table->index('keyword');
            $table->index(['keyword', 'priority']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ledger_keyword_mappings');
    }
};
