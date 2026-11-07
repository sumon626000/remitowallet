<?php

namespace Modules\Merchant\Models;

use App\Models\Currency;
use App\Models\User;
use App\Traits\Notify;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MakePayment extends Model
{
    use HasFactory, Notify;

    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }

    public function merchant()
    {
        return $this->belongsTo(User::class, 'merchant_id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class,'currency_id');
    }

    public function notifyUsersOnPayment($sender): void
    {
        try {
            // Receiver side
            $receivedUser = $this->merchant;
            $paramsTO = [
                'sender' => $sender->name,
                'amount' => getAmount($this->amount),
                'currency' => optional($this->currency)->code,
                'transaction' => $this->trx_id,
            ];
            $action = [
                "name" => $sender->name,
                "image" => $sender?->getImage(),
                "link" => route('user.transaction') ?? '#',
                "icon" => "bi-cash-coin"
            ];
            $firebaseAction = route('user.transaction') ?? '#';

            $this->sendMailSms($receivedUser, 'MAKE_PAYMENT_TO', $paramsTO);
            $this->userPushNotification($receivedUser, 'MAKE_PAYMENT_TO', $paramsTO, $action);
            $this->userFirebasePushNotification($receivedUser, 'MAKE_PAYMENT_TO', $paramsTO, $firebaseAction);

            // Sender side (MAKE_PAYMENT_FROM)
            $paramsFrom = [
                'receiver' => $receivedUser?->name,
                'amount' => getAmount($this->amount),
                'currency' => $this->currency?->code,
                'transaction' => $this->trx_id,
            ];

            $this->sendMailSms($sender, 'MAKE_PAYMENT_FROM', $paramsFrom);
            $this->userPushNotification($sender, 'MAKE_PAYMENT_FROM', $paramsFrom, $action);
            $this->userFirebasePushNotification($sender, 'MAKE_PAYMENT_FROM', $paramsFrom, $firebaseAction);
        } catch (\Exception $e) {

        }
    }


}
