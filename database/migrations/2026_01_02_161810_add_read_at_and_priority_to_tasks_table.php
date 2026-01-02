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
        Schema::table('tasks', function (Blueprint $table) {
            $table->timestamp('read_at')->nullable()->after('status');
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal')->after('read_at');
            $table->text('client_reply')->nullable()->after('description');
            $table->timestamp('replied_at')->nullable()->after('client_reply');
            
            // Add indexes
            $table->index('read_at');
            $table->index('priority');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropIndex(['read_at']);
            $table->dropIndex(['priority']);
            $table->dropColumn(['read_at', 'priority', 'client_reply', 'replied_at']);
        });
    }
};
