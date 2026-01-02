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
            $table->text('admin_reply')->nullable()->after('client_reply');
            $table->timestamp('admin_replied_at')->nullable()->after('admin_reply');
            $table->index('admin_replied_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropIndex(['admin_replied_at']);
            $table->dropColumn(['admin_reply', 'admin_replied_at']);
        });
    }
};
