<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {

        Schema::create('merchant_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('merchant_id')->nullable();
            $table->enum('charge_applied_to', ['user', 'merchant', 'none'])->default('merchant');
            $table->boolean('auto_withdraw')->default(false);
            $table->enum('withdraw_frequency', ['daily', 'weekly', 'monthly'])->default('weekly');

            $table->decimal('withdraw_amount', 18, 8)->default(0.00);
            $table->string('withdraw_currency')->nullable()->default('USD');
            $table->text('withdraw_information')->nullable();

            $table->boolean('status')->default(true);
            $table->timestamp('last_auto_withdraw_at')->nullable();
            $table->timestamps();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('merchant_settings');
    }
};
