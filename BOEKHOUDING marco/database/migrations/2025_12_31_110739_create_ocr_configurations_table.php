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
        Schema::create('ocr_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('document_type')->unique(); // invoice, receipt, form, etc.
            $table->string('engine')->default('tesseract'); // tesseract, aws_textract, google_vision, azure_form_recognizer
            $table->decimal('confidence_threshold', 5, 2)->default(70.00); // Minimum confidence score
            $table->boolean('enabled')->default(true);
            $table->json('engine_settings')->nullable(); // Engine-specific settings
            $table->json('api_keys')->nullable(); // Encrypted API keys
            $table->integer('usage_count')->default(0); // Track usage for cost optimization
            $table->decimal('average_confidence', 5, 2)->nullable(); // Track performance
            $table->decimal('average_processing_time', 8, 2)->nullable(); // In seconds
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ocr_configurations');
    }
};
