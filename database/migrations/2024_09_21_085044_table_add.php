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
        Schema::create('dispute_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dispute_id')->nullable();
            $table->foreignId('user_id')->nullable();
            $table->foreignId('admin_id')->nullable();
            $table->tinyInteger('status')->nullable()->comment("0 = user replied ,1 = admin replied");
            $table->text('message')->nullable();
            $table->text('files')->nullable();
            $table->tinyInteger('action')->nullable()->comment("0 = solved,1 = closed,2 = mute,3 = unmute");
            $table->string('utr')->nullable();
            $table->timestamps();
        });

        Schema::create('escrows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_id')->nullable();
            $table->foreignId('receiver_id')->nullable();
            $table->foreignId('currency_id')->nullable();
            $table->decimal('percentage', 16, 8)->default(0.00000000)->comment("Percent of charge");
            $table->decimal('charge_percentage', 16, 8)->default(0.00000000)->comment("After adding percent of charge");
            $table->decimal('charge_fixed', 16, 8)->default(0.00000000);
            $table->decimal('charge', 16, 8)->default(0.00000000);
            $table->decimal('amount', 16, 8)->default(0.00000000);
            $table->decimal('transfer_amount', 16, 8)->default(0.00000000)->comment("Amount deduct from sender");
            $table->decimal('received_amount', 16, 8)->default(0.00000000)->comment("Amount add to receiver");
            $table->tinyInteger('charge_from')->default(0)->comment("0 = Sender, 1 = Receiver");
            $table->text('note')->nullable();
            $table->text('files')->nullable();
            $table->string('email')->nullable();
            $table->tinyInteger('status')->default(0)->comment("0=Pending, 1=generated, 2 = payment done, 3 = sender request to payment disburse, 4 = payment disbursed,5 = cancel, 6= dispute");
            $table->string('utr')->nullable();
            $table->timestamps();
        });

        Schema::create('request_money', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_id')->nullable();
            $table->foreignId('receiver_id')->nullable();
            $table->foreignId('currency_id')->nullable();
            $table->string('utr')->nullable();
            $table->decimal('amount', 16, 8)->default(0.00000000)->comment("requested amount");
            $table->string('email')->nullable();
            $table->text('note')->nullable();
            $table->tinyInteger('status')->default(0)->comment("0=Pending, 1=generated, 2 = payment done, 3 = sender request to payment disburse, 4 = payment disbursed,5 = cancel, 6= dispute");
            $table->decimal('percentage', 16, 8)->default(0.00000000)->comment("Percent of charge");
            $table->decimal('charge_percentage', 16, 8)->default(0.00000000)->comment("After adding percent of charge");
            $table->decimal('charge_fixed', 16, 8)->default(0.00000000);
            $table->decimal('charge', 16, 8)->default(0.00000000);
            $table->decimal('transfer_amount', 16, 8)->default(0.00000000)->comment("amount deduct from user who (received request)");
            $table->decimal('received_amount', 16, 8)->default(0.00000000)->comment("amount add to user who send (request money)	");
            $table->tinyInteger('charge_from')->default(0)->comment("0 = Sender, 1 = Receiver");
            $table->timestamps();
        });

        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_id')->nullable();
            $table->foreignId('receiver_id')->nullable();
            $table->foreignId('currency_id')->nullable();
            $table->decimal('percentage', 16, 8)->default(0.00000000)->comment("Percent of charge");
            $table->decimal('charge_percentage', 16, 8)->default(0.00000000)->comment("After adding percent of charge");
            $table->decimal('charge_fixed', 16, 8)->default(0.00000000);
            $table->decimal('charge', 16, 8)->default(0.00000000);
            $table->decimal('amount', 16, 8)->default(0.00000000);
            $table->decimal('transfer_amount', 16, 8)->default(0.00000000)->comment("Amount deduct from sender");
            $table->decimal('received_amount', 16, 8)->default(0.00000000)->comment("Amount add to receiver");
            $table->tinyInteger('charge_from')->default(0)->comment("0 = Sender, 1 = Receiver");
            $table->text('note')->nullable();
            $table->text('files')->nullable();
            $table->string('email')->nullable();
            $table->tinyInteger('status')->default(0)->comment("0=Pending, 1=generate, 2 = payment done, 5 = cancel	");
            $table->string('utr')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dispute_details');
        Schema::dropIfExists('escrows');
        Schema::dropIfExists('request_money');
        Schema::dropIfExists('vouchers');
    }
};
