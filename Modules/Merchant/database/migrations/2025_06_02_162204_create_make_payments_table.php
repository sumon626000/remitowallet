<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('make_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id')->nullable();
            $table->unsignedInteger('merchant_id')->nullable();
            $table->unsignedInteger('currency_id')->nullable();

            $table->decimal('amount', 18, 8)->default(0);
            $table->decimal('charge', 18, 8)->default(0);
            $table->decimal('payable_amount', 18, 8)->default(0);
            $table->decimal('received_amount', 18, 8)->default(0);

            $table->string('trx_id')->nullable();
            $table->boolean('status')->default(0);
            $table->text('note')->nullable();

            $table->enum('charge_applied_to', ['user', 'merchant'])->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('make_payments');
    }
};
