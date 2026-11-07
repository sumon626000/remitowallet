<?php

namespace App\Traits;

use App\Models\Escrow;
use Facades\App\Services\BasicService;

trait EscrowTrait
{
    use Notify;

    public function processPaymentCompletion($escrow)
    {
        $escrow->update(['status' => 2]);
        updateWallet($escrow->receiver_id, $escrow->currency_id, $escrow->transfer_amount, 0);

        BasicService::makeTransaction(
            $escrow->receiver, $escrow->currency_id, $escrow->transfer_amount, 0, '-',
            $escrow->utr, 'Balance debited from escrow', $escrow->id, Escrow::class
        );

        $this->sendEscrowNotifications($escrow, 'ESCROW_REQUEST_ACCEPT');
    }

    public function processEscrowCancellation($escrow)
    {
        $escrow->update(['status' => 5]);
        $this->sendEscrowNotifications($escrow, 'ESCROW_REQUEST_CANCEL');
    }

    public function processPaymentDisbursement($escrow)
    {
        $escrow->update(['status' => 4]);
        updateWallet($escrow->sender_id, $escrow->currency_id, $escrow->received_amount, 1);

        BasicService::makeTransaction(
            $escrow->sender, $escrow->currency_id, $escrow->transfer_amount, 0, '+',
            $escrow->utr, 'Balance credited from escrow', $escrow->id, Escrow::class
        );

        $this->sendEscrowNotifications($escrow, 'ESCROW_PAYMENT_DISBURSED');
    }

    public function sendEscrowNotifications($escrow, $eventKey)
    {
        $params = [
            'sender' => optional($escrow->sender)->name,
            'amount' => getAmount($escrow->amount),
            'currency' => optional($escrow->currency)->code,
            'transaction' => $escrow->utr,
        ];

        foreach (['receiver', 'sender'] as $role) {
            $user = $escrow->{$role};
            if ($user) {
                $action = [
                    "name" => $user->fullname,
                    "image" => getFile($user->image_driver, $user->image),
                    "link" => route('user.escrow.index'),
                    "icon" => "fa-light fa-bell-on text-white"
                ];
                $firebaseAction = route('user.escrow.index');

                $templateSuffix = $role === 'sender' ? '_FROM' : '_BY';

                $this->sendMailSms($user, "{$eventKey}{$templateSuffix}", $params);
                $this->userPushNotification($user, "{$eventKey}{$templateSuffix}", $params, $action);
                $this->userFirebasePushNotification($user, "{$eventKey}{$templateSuffix}", $params, $firebaseAction);
            }
        }
    }
}
