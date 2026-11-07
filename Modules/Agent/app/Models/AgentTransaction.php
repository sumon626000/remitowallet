<?php

namespace Modules\Agent\Models;

use App\Models\Currency;
use App\Models\Transaction;
use App\Models\User;
use App\Traits\Notify;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Prunable;

class AgentTransaction extends Model
{
    use HasFactory, Notify, Prunable;

    protected $fillable = [
        'type', 'currency_id', 'user_id', 'agent_id', 'amount', 'charge', 'net_amount', 'commission', 'trx_id', 'status', 'note',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function transaction()
    {
        return $this->morphOne(Transaction::class, 'transactional');
    }

    public function directionIcon(): string
    {
        $authId = auth()->id();
        $isAgent = $this->agent_id == $authId;
        $isUser = $this->user_id == $authId;

        $type = $this->type;

        $isSender = match (true) {
            $isAgent && $type === 'cash_out' => false,
            $isAgent && $type === 'cash_in' => true,
            $isUser && $type === 'cash_in' => false,
            $isUser && $type === 'cash_out' => true,
            default => false,
        };

        $iconClass = $isSender ? 'bi-arrow-up-right' : 'bi-arrow-down-right';
        $colorClass = $isSender ? 'icon-soft-danger' : 'icon-soft-success';
        $label = $isSender ? __('Sent') : __('Received');

        return <<<HTML
        <div class="d-flex align-items-center">
            <span class="icon {$colorClass} icon-sm icon-circle" title="{$label}">
                <i class="bi {$iconClass}"></i>
            </span>
        </div>
    HTML;
    }

    public function notifyUsersOnCashOut($sender): void
    {
        try {
            // Receiver side
            $receivedUser = $this->agent;
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

            $this->sendMailSms($receivedUser, 'CASH_OUT_TO', $paramsTO);
            $this->userPushNotification($receivedUser, 'CASH_OUT_TO', $paramsTO, $action);
            $this->userFirebasePushNotification($receivedUser, 'CASH_OUT_TO', $paramsTO, $firebaseAction);

            // Sender side (CASH_OUT_FROM)
            $paramsFrom = [
                'receiver' => $receivedUser?->name,
                'amount' => getAmount($this->amount),
                'currency' => $this->currency?->code,
                'transaction' => $this->trx_id,
            ];

            $this->sendMailSms($sender, 'CASH_OUT_FROM', $paramsFrom);
            $this->userPushNotification($sender, 'CASH_OUT_FROM', $paramsFrom, $action);
            $this->userFirebasePushNotification($sender, 'CASH_OUT_FROM', $paramsFrom, $firebaseAction);
        } catch (\Exception $e) {

        }
    }

    public function notifyUsersOnCashIn($sender): void
    {
        try {
            // Receiver side
            $receivedUser = $this->user;
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

            $this->sendMailSms($receivedUser, 'CASH_IN_TO', $paramsTO);
            $this->userPushNotification($receivedUser, 'CASH_IN_TO', $paramsTO, $action);
            $this->userFirebasePushNotification($receivedUser, 'CASH_IN_TO', $paramsTO, $firebaseAction);

            // Sender side (CASH_IN_FROM)
            $paramsFrom = [
                'receiver' => $receivedUser?->name,
                'amount' => getAmount($this->amount),
                'currency' => $this->currency?->code,
                'transaction' => $this->trx_id,
            ];

            $this->sendMailSms($sender, 'CASH_IN_FROM', $paramsFrom);
            $this->userPushNotification($sender, 'CASH_IN_FROM', $paramsFrom, $action);
            $this->userFirebasePushNotification($sender, 'CASH_IN_FROM', $paramsFrom, $firebaseAction);

        } catch (\Exception $e) {

        }
    }


    //for api
    public function scopeSearch($query, $filters)
    {
        return $query
            ->when(isset($filters['type']), function ($q) use ($filters) {
                return $q->where('type', $filters['type']);
            })
            ->when(isset($filters['trx_id']), function ($q) use ($filters) {
                return $q->where('trx_id', 'LIKE', "%{$filters['trx_id']}%");
            })
            ->when(isset($filters['min']), function ($q) use ($filters) {
                return $q->where('amount', '>=', $filters['min']);
            })
            ->when(isset($filters['max']), function ($q) use ($filters) {
                return $q->where('amount', '<=', $filters['max']);
            })
            ->when(isset($filters['currency_id']) && $filters['currency_id'] !== 'all', function ($q) use ($filters) {
                return $q->where('currency_id', $filters['currency_id']);
            })
            ->when(isset($filters['created_at']) && preg_match("/^[0-9]{2,4}-[0-9]{1,2}-[0-9]{1,2}$/", $filters['created_at']), function ($q) use ($filters) {
                return $q->whereDate("created_at", $filters['created_at']);
            });
    }

    public function prunable(): Builder
    {
        return static::where('created_at', '<=', now()->subDays(2))->where('status', 0);
    }
}
