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
        Schema::table('users', function (Blueprint $table) {
            $table->string('business_name')->nullable();
            $table->string('vat_number')->nullable();
            $table->text('business_address')->nullable();
            $table->string('business_city')->nullable();
            $table->string('business_postal_code')->nullable();
            $table->string('business_country')->default('NL');
            $table->string('business_phone')->nullable();
            $table->string('business_email')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('currency', 3)->default('EUR');
            $table->string('language', 5)->default('en');
            $table->boolean('onboarding_completed')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'business_name',
                'vat_number',
                'business_address',
                'business_city',
                'business_postal_code',
                'business_country',
                'business_phone',
                'business_email',
                'logo_path',
                'currency',
                'language',
                'onboarding_completed'
            ]);
        });
    }
};
