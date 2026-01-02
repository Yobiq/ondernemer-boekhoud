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
        Schema::table('documents', function (Blueprint $table) {
            $table->string('vat_rubriek', 10)->nullable()->after('vat_rate'); // BTW rubriek: 1a, 1b, 1c, 2a, 3a, 3b, 4a, 5b
            $table->string('vat_code', 20)->nullable()->after('vat_rubriek'); // BTW code: NL21, NL9, NL0, VERL, etc.
            $table->boolean('auto_approved')->default(false)->after('vat_code');
            $table->text('auto_approval_reason')->nullable()->after('auto_approved'); // Waarom auto-approved
            $table->text('review_required_reason')->nullable()->after('auto_approval_reason'); // Waarom handmatige controle nodig
            
            // Indexes
            $table->index('vat_rubriek');
            $table->index('vat_code');
            $table->index('auto_approved');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropIndex(['vat_rubriek']);
            $table->dropIndex(['vat_code']);
            $table->dropIndex(['auto_approved']);
            $table->dropColumn(['vat_rubriek', 'vat_code', 'auto_approved', 'auto_approval_reason', 'review_required_reason']);
        });
    }
};
