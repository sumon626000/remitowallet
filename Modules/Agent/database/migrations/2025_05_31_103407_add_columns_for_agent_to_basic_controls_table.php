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
        Schema::table('basic_controls', function (Blueprint $table) {
            $table->boolean('cash_in')->default(1)->after('bill_payment');
            $table->boolean('cash_out')->default(1)->after('cash_in');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('basic_controls', function (Blueprint $table) {
            $table->dropColumn(['cash_in', 'cash_out']);
        });
    }
};
