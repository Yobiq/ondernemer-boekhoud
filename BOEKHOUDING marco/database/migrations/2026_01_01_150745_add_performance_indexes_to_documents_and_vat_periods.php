<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * PERFORMANCE FIX: Add missing indexes for frequently queried columns
     */
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            // Add indexes for frequently used columns
            if (!$this->indexExists('documents', 'documents_is_paid_index')) {
                $table->index('is_paid', 'documents_is_paid_index');
            }
            
            if (!$this->indexExists('documents', 'documents_supplier_name_index')) {
                $table->index('supplier_name', 'documents_supplier_name_index');
            }
            
            if (!$this->indexExists('documents', 'documents_vat_rubriek_index')) {
                $table->index('vat_rubriek', 'documents_vat_rubriek_index');
            }
            
            // document_type already has index from previous migration, but verify
            // is_paid and supplier_name are new indexes needed for performance
        });
        
        // Check if vat_period_documents table exists and add index
        if (Schema::hasTable('vat_period_documents')) {
            Schema::table('vat_period_documents', function (Blueprint $table) {
                // rubriek index already exists, but verify unique constraint
                // The unique constraint on [vat_period_id, document_id] already exists from original migration
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropIndex('documents_is_paid_index');
            $table->dropIndex('documents_supplier_name_index');
            $table->dropIndex('documents_vat_rubriek_index');
        });
    }
    
    /**
     * Check if index exists (SQLite compatible)
     */
    private function indexExists(string $table, string $indexName): bool
    {
        $connection = Schema::getConnection();
        $database = $connection->getDatabaseName();
        
        if ($connection->getDriverName() === 'sqlite') {
            // SQLite: Check in sqlite_master
            $result = $connection->selectOne(
                "SELECT name FROM sqlite_master WHERE type='index' AND name=? AND tbl_name=?",
                [$indexName, $table]
            );
            return $result !== null;
        } else {
            // PostgreSQL/MySQL: Check information_schema
            $result = $connection->selectOne(
                "SELECT COUNT(*) as count FROM information_schema.statistics 
                 WHERE table_schema = ? AND table_name = ? AND index_name = ?",
                [$database, $table, $indexName]
            );
            return ($result->count ?? 0) > 0;
        }
    }
};
