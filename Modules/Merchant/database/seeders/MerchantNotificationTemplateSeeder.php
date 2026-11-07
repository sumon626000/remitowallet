<?php

namespace Modules\Merchant\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class MerchantNotificationTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now()->toDateTimeString();
        $languageId = 1;
        $langCode = 'en';
        $emailFrom = 'support@paysecure.net';

        $rows = [
            [
                'type' => 2,
                'template_key' => 'MAKE_PAYMENT_FROM',
                'name' => 'Cash Out Sent',
                'subject' => 'You have made a payment',
                'email' => 'Payment sent: [[amount]] [[currency]]. Trx: #[[transaction]]',
                'sms' => 'Payment sent: [[amount]] [[currency]]. Trx: #[[transaction]]',
                'in_app' => 'Payment sent: [[amount]] [[currency]]. Trx: #[[transaction]]',
                'push' => 'Payment sent: [[amount]] [[currency]]. Trx: #[[transaction]]',
                'short_keys' => json_encode([
                    'receiver' => 'Receiver Name',
                    'amount' => 'Payment Amount',
                    'currency' => 'Currency Code',
                    'transaction' => 'Transaction ID',
                ]),
            ],
            [
                'type' => 4,
                'template_key' => 'MAKE_PAYMENT_TO',
                'name' => 'Payment Received',
                'subject' => 'You have received a cash out',
                'email' => 'Payment received [[amount]] [[currency]] from [[sender]]',
                'sms' => 'Payment received [[amount]] [[currency]] from [[sender]]',
                'in_app' => 'Payment received [[amount]] [[currency]] from [[sender]]',
                'push' => 'Payment received [[amount]] [[currency]] from [[sender]]',
                'short_keys' => json_encode([
                    'sender' => 'Sender Name',
                    'amount' => 'Payment Amount',
                    'currency' => 'Currency Code',
                    'transaction' => 'Transaction ID',
                ]),
            ]
        ];


        foreach ($rows as $row) {
            DB::table('notification_templates')->updateOrInsert(
                ['template_key' => $row['template_key'], 'language_id' => $languageId],
                array_merge([
                    'lang_code' => $langCode,
                    'email_from' => $emailFrom,
                    'notify_for' => 0,
                    'status' => json_encode(['mail' => 1, 'sms' => 1, 'in_app' => 1, 'push' => 1]),
                    'created_at' => $now,
                    'updated_at' => $now,
                ], $row)
            );
        }

    }
}

