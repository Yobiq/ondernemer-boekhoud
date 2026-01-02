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
        Schema::table('clients', function (Blueprint $table) {
            $table->string('default_vat_period_type')->default('quarterly')->after('active'); // monthly or quarterly
            $table->string('vat_submission_method')->nullable()->after('default_vat_period_type'); // digital, paper, etc.
            $table->boolean('auto_approval_enabled')->default(true)->after('vat_submission_method');
            $table->decimal('auto_approval_threshold', 5, 2)->default(85.00)->after('auto_approval_enabled'); // Minimum confidence for auto-approval
            $table->boolean('email_notifications_enabled')->default(true)->after('auto_approval_threshold');
            $table->json('notification_preferences')->nullable()->after('email_notifications_enabled'); // Custom notification settings
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn([
                'default_vat_period_type',
                'vat_submission_method',
                'auto_approval_enabled',
                'auto_approval_threshold',
                'email_notifications_enabled',
                'notification_preferences',
            ]);
        });
    }
};
