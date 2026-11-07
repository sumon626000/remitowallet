<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::table('notification_templates', function (Blueprint $table) {
            $table->tinyInteger('type')->default(1)
                ->comment('1: All, 2: User, 3: Agent, 4: Merchant, 5: User + Agent, 6: User + Merchant, 7: Agent + Merchant')
                ->after('notify_for');
        });

        $typeMap = [
            "ADD_BALANCE" => 1,
            "KYC_APPROVED" => 1,
            "KYC_REJECTED" => 1,
            "ADMIN_REPLIED_TICKET" => 1,
            "PAYMENT_REJECTED" => 5,
            "ADD_FUND_USER_USER" => 5,
            "PAYOUT_REQUEST_FROM" => 1,
            "PAYOUT_APPROVED" => 1,
            "PAYOUT_CANCEL" => 1,
            "PASSWORD_RESET" => 1,
            "VERIFICATION_CODE" => 1,
            "TWO_STEP_ENABLED" => 1,
            "TWO_STEP_DISABLED" => 1,
            "DISPUTE_REQUEST_TO_USER" => 2,
            "TRANSFER_TO" => 2,
            "TRANSFER_FROM" => 2,
            "REQUEST_MONEY_INIT" => 2,
            "REQUEST_MONEY_CONFIRM" => 2,
            "REQUEST_MONEY_CANCEL" => 2,
            "MONEY_EXCHANGE" => 2,
            "REDEEM_CODE_GENERATE" => 2,
            "REDEEM_CODE_USED_BY" => 2,
            "REDEEM_CODE_SENDER" => 2,
            "ESCROW_REQUEST_SENDER" => 2,
            "ESCROW_REQUEST_RECEIVER" => 2,
            "ESCROW_REQUEST_ACCEPT_FROM" => 2,
            "ESCROW_REQUEST_ACCEPT_BY" => 2,
            "ESCROW_REQUEST_CANCEL_FROM" => 2,
            "ESCROW_REQUEST_CANCEL_BY" => 2,
            "ESCROW_PAYMENT_DISBURSED_REQUEST_FROM" => 2,
            "ESCROW_PAYMENT_DISBURSED_REQUEST_BY" => 2,
            "ESCROW_PAYMENT_DISBURSED_FROM" => 2,
            "ESCROW_PAYMENT_DISBURSED_BY" => 2,
            "DISPUTE_REQUEST_TO_ADMIN" => 2,
            "VOUCHER_PAYMENT_REQUEST_TO" => 2,
            "VOUCHER_PAYMENT_REQUEST_FROM" => 2,
            "VOUCHER_PAYMENT_TO" => 2,
            "VOUCHER_PAYMENT_FROM" => 2,
            "VOUCHER_PAYMENT_CANCEL_TO" => 2,
            "VOUCHER_PAYMENT_CANCEL_FROM" => 2,
            "PAYOUT_CONFIRM" => 1,
            "DEPOSIT_BONUS" => 2,
            "LOGIN_BONUS" => 2,
            "INVOICE_PAYMENT_TO" => 2,
            "INVOICE_PAYMENT_FROM" => 2,
            "PRODUCT_ORDER" => 2,
            "PRODUCT_ORDER_BUYER" => 2,
            "CONTACT_MESSAGE" => 2,
            "QR_PAYMENT_TO" => 2,
            "QR_PAYMENT_FROM" => 2,
            "VIRTUAL_CARD_APPLY" => 2,
            "VIRTUAL_CARD_REJECTED" => 2,
            "VIRTUAL_CARD_APPROVE" => 2,
            "VIRTUAL_CARD_FUND_RETURN" => 2,
            "VIRTUAL_CARD_FUND_APPROVE" => 2,
            "VIRTUAL_CARD_UNBLOCK" => 2,
            "VIRTUAL_CARD_BLOCK" => 2,
            "BILL_PAYMENT_RETURN" => 2,
            "BILL_PAY" => 2,
            "PAYOUT_FAIL" => 1,
            "API_PAYMENT" => 4,
        ];

        foreach ($typeMap as $templateKey => $type) {
            DB::table('notification_templates')
                ->where('template_key', $templateKey)
                ->update(['type' => $type]);
        }

    }

    public function down()
    {
        Schema::table('notification_templates', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }

};
