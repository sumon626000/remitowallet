<?php

namespace App\Jobs;

use App\Models\CommissionEntry;
use App\Models\ReferralBonus;
use App\Models\Transaction;
use App\Models\User;
use App\Traits\Notify;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Str;

class BonusCommissionJob implements ShouldQueue
{
    use Queueable, Notify;

    protected $id, $amount, $commissionType;

    public function __construct($id, $amount, $commissionType = '')
    {
        $this->id = $id;
        $this->amount = $amount;
        $this->commissionType = $commissionType;
    }

    public function handle()
    {
        $basicControl = basicControl();
        $user = User::find($this->id);
        if (!$user) return;

        $levels = ReferralBonus::where('referral_on', $this->commissionType)
            ->where('status', 1)
            ->orderBy('level')->get();

        if ($levels->isEmpty()) return;

        $usr = $this->id;
        $i = 1;
        $commissionsToInsert = [];
        $transactionsToInsert = [];

        while ($usr && $i <= $levels->count()) {
            $me = User::find($usr);
            if (!$me || !$me->referral_id) break;

            $refer = User::find($me->referral_id);
            if (!$refer) break;

            $commission = $levels->where('level', $i)->first();
            if (!$commission) break;

            if (CommissionEntry::where([
                'to_user' => $refer->id,
                'from_user' => $this->id,
                'type' => $this->commissionType
            ])->exists()) break;

            $commissionAmount = ($commission->calcType == 0)
                ? ($this->amount * $commission->amount) / 100
                : $commission->amount;

            $utr = generateOrderedId('commission_entries','utr','C');

            $commissionsToInsert[] = [
                'to_user' => $refer->id,
                'from_user' => $this->id,
                'level' => $i,
                'commission_amount' => getAmount($commissionAmount),
                'title' => 'Level ' . $i . ' Referral Commission From ' . $user->username,
                'type' => $this->commissionType,
                'currency_id' => $basicControl->currency?->id,
                'utr' => $utr,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $transactionsToInsert[] = [
                'user_id' => $refer->id,
                'trx_type' => '+',
                'trx_id' => $utr,
                'remarks' => 'Referral Commission From ' . $user->username,
                'amount' => getAmount($commissionAmount),
                'charge' => 0,
                'currency_id' => $basicControl->currency?->id,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            updateWallet($refer->id, $basicControl->currency?->id, getAmount($commissionAmount), 1);

            // Send notifications
            $params = [
                'sender' => $user->username,
                'amount' => getAmount($commissionAmount),
                'currency' => optional($basicControl->currency)->code,
                'transaction' => $utr,
            ];
            $action = ["link" => '#', "icon" => "fa fa-money-bill-alt text-white"];
            $firebaseAction = '#';
            $this->sendMailSms($refer, 'DEPOSIT_BONUS', $params);
            $this->userPushNotification($refer, 'DEPOSIT_BONUS', $params, $action);
            $this->userFirebasePushNotification($refer, 'DEPOSIT_BONUS', $params, $firebaseAction);

            $usr = $refer->id;
            $i++;
        }

        if (!empty($commissionsToInsert)) CommissionEntry::insert($commissionsToInsert);
        if (!empty($transactionsToInsert)) Transaction::insert($transactionsToInsert);
    }
}
