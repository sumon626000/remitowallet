<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fx_assets', function (Blueprint $table) {
            $table->id();
            $table->string('symbol', 20)->unique();
            $table->string('name', 80);
            $table->string('yahoo_symbol', 40);
            $table->string('twelve_symbol', 40);
            $table->unsignedTinyInteger('decimals')->default(5);
            $table->boolean('active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('fx_trades', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('wallet_id')->nullable();
            $table->unsignedBigInteger('currency_id')->nullable();
            $table->string('asset', 20);
            $table->string('direction', 8);
            $table->decimal('stake', 16, 2);
            $table->decimal('payout_pct', 8, 2);
            $table->decimal('open_price', 20, 8);
            $table->decimal('close_price', 20, 8)->nullable();
            $table->timestamp('open_time');
            $table->timestamp('expire_time')->index();
            $table->string('status', 12)->default('open')->index();
            $table->string('result', 12)->nullable();
            $table->decimal('payout_amount', 16, 2)->default(0);
            $table->timestamp('settled_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'status']);
        });

        $now = now();
        $rows = [
            ['EUR/USD', 'Euro / US Dollar', 'EURUSD=X', 'EUR/USD', 5],
            ['GBP/USD', 'British Pound / US Dollar', 'GBPUSD=X', 'GBP/USD', 5],
            ['USD/JPY', 'US Dollar / Japanese Yen', 'USDJPY=X', 'USD/JPY', 3],
            ['USD/CHF', 'US Dollar / Swiss Franc', 'USDCHF=X', 'USD/CHF', 5],
            ['AUD/USD', 'Australian Dollar / US Dollar', 'AUDUSD=X', 'AUD/USD', 5],
            ['USD/CAD', 'US Dollar / Canadian Dollar', 'USDCAD=X', 'USD/CAD', 5],
            ['NZD/USD', 'New Zealand Dollar / US Dollar', 'NZDUSD=X', 'NZD/USD', 5],
            ['EUR/GBP', 'Euro / British Pound', 'EURGBP=X', 'EUR/GBP', 5],
            ['EUR/JPY', 'Euro / Japanese Yen', 'EURJPY=X', 'EUR/JPY', 3],
            ['GBP/JPY', 'British Pound / Japanese Yen', 'GBPJPY=X', 'GBP/JPY', 3],
            ['AUD/JPY', 'Australian Dollar / Japanese Yen', 'AUDJPY=X', 'AUD/JPY', 3],
            ['Gold/USD', 'Gold', 'GC=F', 'XAU/USD', 2],
            ['Silver/USD', 'Silver', 'SI=F', 'XAG/USD', 3],
            ['Platinum/USD', 'Platinum', 'PL=F', 'XPT/USD', 2],
            ['Palladium/USD', 'Palladium', 'PA=F', 'XPD/USD', 2],
            ['Oil/USD', 'Crude Oil', 'CL=F', 'WTI/USD', 2],
        ];

        $insert = [];
        foreach ($rows as $i => $row) {
            $insert[] = [
                'symbol' => $row[0],
                'name' => $row[1],
                'yahoo_symbol' => $row[2],
                'twelve_symbol' => $row[3],
                'decimals' => $row[4],
                'active' => 1,
                'sort_order' => $i + 1,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        DB::table('fx_assets')->insert($insert);
    }

    public function down(): void
    {
        Schema::dropIfExists('fx_trades');
        Schema::dropIfExists('fx_assets');
    }
};
