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
        Schema::create('vat_period_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vat_period_id')->constrained()->cascadeOnDelete();
            $table->foreignId('document_id')->constrained()->cascadeOnDelete();
            $table->string('rubriek', 10); // BTW rubriek: 1a, 1b, 1c, 2a, 3a, 3b, 4a, 5b
            $table->string('btw_code', 20)->nullable(); // BTW code: NL21, NL9, NL0, VERL, etc.
            $table->timestamps();
            
            // Indexes
            $table->index('vat_period_id');
            $table->index('document_id');
            $table->index('rubriek');
            
            // Unique constraint: een document kan maar één keer per periode voorkomen
            $table->unique(['vat_period_id', 'document_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vat_period_documents');
    }
};
