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
        Schema::create('agent_transactions', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['cash_in', 'cash_out']);
            $table->unsignedBigInteger('currency_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable(); // customer
            $table->unsignedBigInteger('agent_id')->nullable(); // agent
            $table->decimal('amount', 18, 8)->default(0);
            $table->decimal('charge', 18, 8)->default(0);
            $table->decimal('net_amount', 18, 8)->default(0);
            $table->decimal('commission', 18, 8)->default(0);
            $table->string('trx_id')->nullable();
            $table->boolean('status')->default(0);
            $table->string('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agent_transactions');
    }
};
