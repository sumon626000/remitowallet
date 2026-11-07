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
        Schema::create('merchant_global_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('currency_id');

            $table->decimal('fixed_charge', 18, 8)->default(0.00);
            $table->decimal('percent_charge', 5, 2)->default(0.00);
            $table->boolean('status')->default(true);

            $table->boolean('auto_withdraw')->default(true);
            $table->decimal('min_withdraw_amount', 18, 8)->default(0.00);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('merchant_global_settings');
    }
};
