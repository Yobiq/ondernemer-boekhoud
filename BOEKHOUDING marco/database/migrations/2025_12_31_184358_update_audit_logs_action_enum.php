<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * SQLite doesn't support ALTER ENUM, so we need to recreate the table
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            // SQLite: Recreate table with new enum values
            DB::statement('
                CREATE TABLE audit_logs_new (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    entity_type TEXT NOT NULL,
                    entity_id INTEGER NOT NULL,
                    action TEXT NOT NULL CHECK(action IN ("created", "updated", "approved", "locked", "upload", "xml_export")),
                    old_values TEXT,
                    new_values TEXT,
                    user_id INTEGER,
                    model_type TEXT,
                    model_id INTEGER,
                    metadata TEXT,
                    ip_address TEXT,
                    user_agent TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES users(id)
                )
            ');
            
            DB::statement('
                INSERT INTO audit_logs_new 
                SELECT * FROM audit_logs
            ');
            
            DB::statement('DROP TABLE audit_logs');
            DB::statement('ALTER TABLE audit_logs_new RENAME TO audit_logs');
            
            // Recreate indexes
            DB::statement('CREATE INDEX audit_logs_entity_type_entity_id_index ON audit_logs(entity_type, entity_id)');
            DB::statement('CREATE INDEX audit_logs_user_id_index ON audit_logs(user_id)');
            DB::statement('CREATE INDEX audit_logs_created_at_index ON audit_logs(created_at)');
        } else {
            // For other databases (MySQL, PostgreSQL), we can use ALTER TABLE
            // But since we're using SQLite, this won't run
            Schema::table('audit_logs', function (Blueprint $table) {
                // For MySQL/PostgreSQL, you would alter the enum here
                // But SQLite doesn't support this, so we handle it above
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            // Revert to original enum values
            DB::statement('
                CREATE TABLE audit_logs_old (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    entity_type TEXT NOT NULL,
                    entity_id INTEGER NOT NULL,
                    action TEXT NOT NULL CHECK(action IN ("created", "updated", "approved", "locked")),
                    old_values TEXT,
                    new_values TEXT,
                    user_id INTEGER,
                    model_type TEXT,
                    model_id INTEGER,
                    metadata TEXT,
                    ip_address TEXT,
                    user_agent TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES users(id)
                )
            ');
            
            // Only copy back records with valid actions
            DB::statement('
                INSERT INTO audit_logs_old 
                SELECT * FROM audit_logs 
                WHERE action IN ("created", "updated", "approved", "locked")
            ');
            
            DB::statement('DROP TABLE audit_logs');
            DB::statement('ALTER TABLE audit_logs_old RENAME TO audit_logs');
            
            // Recreate indexes
            DB::statement('CREATE INDEX audit_logs_entity_type_entity_id_index ON audit_logs(entity_type, entity_id)');
            DB::statement('CREATE INDEX audit_logs_user_id_index ON audit_logs(user_id)');
            DB::statement('CREATE INDEX audit_logs_created_at_index ON audit_logs(created_at)');
        }
    }
};
