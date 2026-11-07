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
        Schema::create('agent_settings', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['cash_in', 'cash_out']);
            $table->unsignedBigInteger('currency_id')->nullable();
            // Charge only applied for cash_out
            $table->decimal('fixed_charge', 18, 8)->nullable()->default(0.00);
            $table->decimal('percent_charge', 5, 2)->nullable()->default(0.00); // % like 1.50
            // Commission for agent (applies to both cash_in and cash_out)
            $table->decimal('fixed_commission', 18, 8)->nullable()->default(0.00);
            $table->decimal('percent_commission', 5, 2)->nullable()->default(0.00);

            $table->decimal('min_limit', 18, 8)->nullable()->default(0.00);
            $table->decimal('max_limit', 18, 8)->nullable()->default(0.00);
            $table->decimal('daily_limit', 16, 2)->nullable();
            $table->decimal('monthly_limit', 16, 2)->nullable();
            $table->boolean('status')->default(true);
            $table->enum('charge_applied_to', ['user', 'admin', 'none'])->default('user');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agent_settings');
    }
};
