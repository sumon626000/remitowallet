<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('deposits', function (Blueprint $table) {
            $table->string('receive_currency_code')->nullable()->comment("which wallet currency receive amount");
            $table->decimal('receive_amount', 18, 8)->default(0.00)->comment("how much receive wallet add");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deposits', function (Blueprint $table) {
            $table->dropColumn(['receive_currency_code', 'receive_amount']);
        });
    }
};
