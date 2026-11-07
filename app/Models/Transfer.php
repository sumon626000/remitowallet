<?php

namespace App\Models;

use App\Traits\Notify;
use App\Traits\RandomCode;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;

class Transfer extends Model
{
    use HasFactory, RandomCode, Notify, Prunable;

    public function transactional()
    {
        return $this->morphOne(Transaction::class, 'transactional');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id', 'id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id', 'id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id', 'id');
    }

    public function scopeVisibleToUser($query, $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('sender_id', $userId)
                ->orWhere(function ($subQuery) use ($userId) {
                    $subQuery->where('receiver_id', $userId)
                        ->where('status', 1);
                });
        });
    }

    public function scopeGetProfit($query, $days = null)
    {
        $baseCurrencyRate = "(SELECT exchange_rate FROM currencies WHERE currencies.id = transfers.currency_id LIMIT 1)";

        if ($days) {
            $date = now()->subDays($days)->toDateString();

            return $query->selectRaw("
                SUM(
                    CASE
                        WHEN status = 1
                        THEN charge / {$baseCurrencyRate}
                        ELSE 0
                    END
                ) AS total_profit
            ")->selectRaw("
                SUM(
                    CASE
                        WHEN status = 1 AND updated_at >= ?
                        THEN charge / {$baseCurrencyRate}
                        ELSE 0
                    END
                ) AS profit_{$days}_days
            ", [$date])->selectRaw("
                (SUM(
                    CASE
                        WHEN status = 1 AND updated_at >= ?
                        THEN charge / {$baseCurrencyRate}
                        ELSE 0
                    END
                ) / NULLIF(SUM(
                    CASE
                        WHEN status = 1
                        THEN charge / {$baseCurrencyRate}
                        ELSE 0
                    END
                ), 0)) * 100 AS profit_percentage_{$days}_days
            ", [$date]);
        }

        return $query->selectRaw("
            SUM(
                CASE
                    WHEN status = 1
                    THEN charge / {$baseCurrencyRate}
                    ELSE 0
                END
            ) AS total_profit
        ");
    }

    public function scopeFilterTransfers($query, $search, $userId)
    {
        return $query
            ->when(isset($search['email']), function ($query) use ($search) {
                return $query->where('email', 'LIKE', "%{$search['email']}%");
            })
            ->when(isset($search['utr']), function ($query) use ($search) {
                return $query->where('utr', 'LIKE', "%{$search['utr']}%");
            })
            ->when(isset($search['min']), function ($query) use ($search) {
                return $query->where('amount', '>=', $search['min']);
            })
            ->when(isset($search['max']), function ($query) use ($search) {
                return $query->where('amount', '<=', $search['max']);
            })
            ->when(isset($search['currency_id']), function ($query) use ($search) {
                return $query->where('currency_id', $search['currency_id']);
            })
            ->when(isset($search['sender']), function ($query) use ($search) {
                return $query->whereHas('sender', function ($qry) use ($search) {
                    $qry->where('username', 'LIKE', "%{$search['sender']}%");
                });
            })
            ->when(isset($search['receiver']), function ($query) use ($search) {
                return $query->whereHas('receiver', function ($qry) use ($search) {
                    $qry->where('username', 'LIKE', "%{$search['receiver']}%");
                });
            })
            ->when(isset($search['status']), function ($query) use ($search) {
                return $query->where('status', $search['status']);
            })
            ->when(isset($search['type']) && preg_match("/sent/", $search['type']), function ($query) use ($userId) {
                return $query->where('sender_id', $userId);
            })
            ->when(isset($search['type']) && preg_match("/received/", $search['type']), function ($query) use ($userId) {
                return $query->where('receiver_id', $userId);
            })
            ->when(isset($search['created_at']) && preg_match("/^[0-9]{2,4}-[0-9]{1,2}-[0-9]{1,2}$/", $search['created_at']), function ($query) use ($search) {
                return $query->whereDate('created_at', $search['created_at']);
            });
    }


    public function type($pm = null)
    {
        $isSender = ($this->sender_id == auth()->id());

        if ($pm === 'type') {
            return $isSender ? trans('Sent') : trans('Received');
        }
        if ($pm === 'user') {
            return $isSender ? trans('Receiver') : trans('Sender');
        }
        return $isSender;
    }

    public function notifyUsersOnTransfer($sender): void
    {
        // Receiver side (TRANSFER_TO)
        $receivedUserTO = $this->receiver;
        $paramsTO = [
            'sender' => $sender->name,
            'amount' => getAmount($this->received_amount),
            'currency' => optional($this->currency)->code,
            'transaction' => $this->utr,
        ];
        $action = [
            "name" => $this->sender?->name,
            "image" => $this->sender?->getImage(),
            "link" => route('user.transfer.index') ?? '#',
            "icon" => "fa-light fa-bell-on text-white"
        ];
        $firebaseAction = route('user.transfer.index') ?? '#';

        $this->sendMailSms($receivedUserTO, 'TRANSFER_TO', $paramsTO);
        $this->userPushNotification($receivedUserTO, 'TRANSFER_TO', $paramsTO, $action);
        $this->userFirebasePushNotification($receivedUserTO, 'TRANSFER_TO', $paramsTO, $firebaseAction);

        // Sender side (TRANSFER_FROM)
        $receivedUserFrom = $sender;
        $paramsFrom = [
            'receiver' => optional($this->receiver)->name,
            'amount' => getAmount($this->received_amount),
            'currency' => optional($this->currency)->code,
            'transaction' => $this->utr,
        ];

        $this->sendMailSms($receivedUserFrom, 'TRANSFER_FROM', $paramsFrom);
        $this->userPushNotification($receivedUserFrom, 'TRANSFER_FROM', $paramsFrom, $action);
        $this->userFirebasePushNotification($receivedUserFrom, 'TRANSFER_FROM', $paramsFrom, $firebaseAction);
    }


    public function prunable(): Builder
    {
        return static::where('created_at', '<=', now()->subDays(2))->where('status', 0);
    }


}
