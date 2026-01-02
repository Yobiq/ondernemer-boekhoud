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
            $table->string('company_name')->nullable()->after('name');
            $table->string('phone')->nullable()->after('email');
            $table->string('vat_number')->nullable()->after('kvk_number');
            $table->string('address_line1')->nullable()->after('vat_number');
            $table->string('address_line2')->nullable()->after('address_line1');
            $table->string('postal_code')->nullable()->after('address_line2');
            $table->string('city')->nullable()->after('postal_code');
            $table->string('country')->default('Nederland')->after('city');
            $table->string('website')->nullable()->after('country');
            $table->text('notes')->nullable()->after('website');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn([
                'company_name',
                'phone',
                'vat_number',
                'address_line1',
                'address_line2',
                'postal_code',
                'city',
                'country',
                'website',
                'notes',
            ]);
        });
    }
};
