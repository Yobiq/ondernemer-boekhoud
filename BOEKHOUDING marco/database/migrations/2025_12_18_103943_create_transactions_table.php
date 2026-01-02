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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('bank_reference')->unique(); // Unique transaction reference
            $table->decimal('amount', 12, 2);
            $table->date('transaction_date');
            $table->string('iban')->nullable();
            $table->string('counterparty_name')->nullable();
            $table->text('description')->nullable();
            
            // Matching with documents
            $table->foreignId('matched_document_id')->nullable()->constrained('documents');
            
            $table->timestamps();
            
            // Indexes
            $table->index('client_id');
            $table->index('transaction_date');
            $table->index('matched_document_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
