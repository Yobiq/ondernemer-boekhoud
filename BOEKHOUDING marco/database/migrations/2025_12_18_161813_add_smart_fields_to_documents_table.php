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
            $table->enum('document_type', [
                'purchase_invoice',
                'receipt',
                'bank_statement', 
                'sales_invoice',
                'other'
            ])->nullable()->after('status');
            
            $table->enum('upload_source', [
                'web',
                'mobile_camera'
            ])->nullable()->after('document_type');
            
            $table->index('document_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn(['document_type', 'upload_source']);
        });
    }
};
