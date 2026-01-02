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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('file_path'); // Path to document in storage
            $table->string('original_filename');
            $table->enum('status', ['pending', 'ocr_processing', 'review_required', 'approved', 'archived', 'task_opened'])
                ->default('pending');
            
            // Financial amounts
            $table->decimal('amount_excl', 12, 2)->nullable(); // Excluding BTW
            $table->decimal('amount_vat', 12, 2)->nullable(); // BTW amount
            $table->decimal('amount_incl', 12, 2)->nullable(); // Including BTW
            $table->enum('vat_rate', ['21', '9', '0', 'verlegd'])->nullable();
            
            // Ledger classification
            $table->foreignId('ledger_account_id')->nullable()->constrained('ledger_accounts');
            $table->decimal('confidence_score', 5, 2)->nullable(); // 0-100
            
            // OCR and metadata
            $table->jsonb('ocr_data')->nullable(); // Normalized OCR output
            $table->date('document_date')->nullable();
            $table->string('supplier_name')->nullable();
            $table->string('supplier_vat')->nullable();
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index('status');
            $table->index('client_id');
            $table->index('document_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
