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
        Schema::create('vat_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->date('period_start');
            $table->date('period_end');
            $table->enum('status', ['open', 'voorbereid', 'ingediend', 'afgesloten'])->default('open');
            $table->foreignId('prepared_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('prepared_at')->nullable();
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('closed_at')->nullable();
            $table->integer('year'); // Voor filtering (bijv. 2024)
            $table->integer('quarter')->nullable(); // 1-4 of null voor maandelijkse periodes
            $table->integer('month')->nullable(); // 1-12 of null voor kwartaalperiodes
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Indexes voor performance
            $table->index('client_id');
            $table->index('status');
            $table->index('year');
            $table->index('quarter');
            $table->index('month');
            $table->index(['client_id', 'year', 'quarter']);
            $table->index(['client_id', 'year', 'month']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vat_periods');
    }
};
