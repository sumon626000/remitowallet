<?php

namespace Modules\Agent\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AgentNotificationTemplateSeeder extends Seeder
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
                'template_key' => 'CASH_OUT_FROM',
                'name' => 'Cash Out Sent',
                'subject' => 'You have sent a cash out',
                'email' => 'Cash out sent: [[amount]] [[currency]]. Trx: #[[transaction]]',
                'sms' => 'Cash out sent: [[amount]] [[currency]]. Trx: #[[transaction]]',
                'in_app' => 'Cash out sent: [[amount]] [[currency]]. Trx: #[[transaction]]',
                'push' => 'Cash out sent: [[amount]] [[currency]]. Trx: #[[transaction]]',
                'short_keys' => json_encode([
                    'receiver' => 'Receiver Name',
                    'amount' => 'Cash Out Amount',
                    'currency' => 'Currency Code',
                    'transaction' => 'Transaction ID',
                ]),
            ],
            [
                'type' => 3,
                'template_key' => 'CASH_OUT_TO',
                'name' => 'Cash Out Received',
                'subject' => 'You have received a cash out',
                'email' => 'Cash out received [[amount]] [[currency]] from [[sender]]',
                'sms' => 'Cash out received [[amount]] [[currency]] from [[sender]]',
                'in_app' => 'Cash out received [[amount]] [[currency]] from [[sender]]',
                'push' => 'Cash out received [[amount]] [[currency]] from [[sender]]',
                'short_keys' => json_encode([
                    'sender' => 'Sender Name',
                    'amount' => 'Cash Out Amount',
                    'currency' => 'Currency Code',
                    'transaction' => 'Transaction ID',
                ]),
            ],
            [
                'type' => 3,
                'template_key' => 'CASH_IN_FROM',
                'name' => 'Cash In Sent',
                'subject' => 'You have sent a cash in',
                'email' => 'Cash in sent: [[amount]] [[currency]] to [[receiver]]',
                'sms' => 'Cash in sent: [[amount]] [[currency]] to [[receiver]]',
                'in_app' => 'Cash in sent: [[amount]] [[currency]] to [[receiver]]',
                'push' => 'Cash in sent: [[amount]] [[currency]] to [[receiver]]',
                'short_keys' => json_encode([
                    'receiver' => 'Receiver Name',
                    'amount' => 'Cash In Amount',
                    'currency' => 'Currency Code',
                    'transaction' => 'Transaction ID',
                ]),
            ],
            [
                'type' => 2,
                'template_key' => 'CASH_IN_TO',
                'name' => 'Cash In Received',
                'subject' => 'You have received a cash in',
                'email' => 'Cash in received [[amount]] [[currency]] from [[sender]]',
                'sms' => 'Cash in received [[amount]] [[currency]] from [[sender]]',
                'in_app' => 'Cash in received [[amount]] [[currency]] from [[sender]]',
                'push' => 'Cash in received [[amount]] [[currency]] from [[sender]]',
                'short_keys' => json_encode([
                    'sender' => 'Sender Name',
                    'amount' => 'Cash In Amount',
                    'currency' => 'Currency Code',
                    'transaction' => 'Transaction ID',
                ]),
            ],
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

