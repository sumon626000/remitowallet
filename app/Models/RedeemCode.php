<?php

namespace App\Models;

use App\Traits\ProfitQueryTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Support\Facades\Auth;

class RedeemCode extends Model
{
    use HasFactory, Prunable, ProfitQueryTrait;

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

    public function scopeFilter($query, $filters)
    {
        return $query->when(isset($filters['email']), fn($q) => $q->where('email', 'LIKE', "%{$filters['email']}%"))
            ->when(isset($filters['utr']), fn($q) => $q->where('utr', 'LIKE', "%{$filters['utr']}%"))
            ->when(isset($filters['min']), fn($q) => $q->where('amount', '>=', $filters['min']))
            ->when(isset($filters['max']), fn($q) => $q->where('amount', '<=', $filters['max']))
            ->when(isset($filters['currency_id']), fn($q) => $q->where('currency_id', $filters['currency_id']))
            ->when(isset($filters['sender']), function ($q) use ($filters) {
                $q->whereHas('sender', function ($qry) use ($filters) {
                    $qry->whereRaw("CONCAT(firstname, ' ', lastname) LIKE ?", ["%{$filters['sender']}%"])
                        ->orWhere('username', 'LIKE', "%{$filters['sender']}%");
                });
            })
            ->when(isset($filters['receiver']), function ($q) use ($filters) {
                $q->whereHas('receiver', function ($qry) use ($filters) {
                    $qry->whereRaw("CONCAT(firstname, ' ', lastname) LIKE ?", ["%{$filters['receiver']}%"])
                        ->orWhere('username', 'LIKE', "%{$filters['receiver']}%");
                });
            })
            ->when(isset($filters['type']) && preg_match("/sent/", $filters['type']), fn($q) => $q->where("sender_id", Auth::id()))
            ->when(isset($filters['type']) && preg_match("/received/", $filters['type']), fn($q) => $q->where("receiver_id", Auth::id()))
            ->when(isset($filters['created_at']) && preg_match("/^[0-9]{2,4}-[0-9]{1,2}-[0-9]{1,2}$/", $filters['created_at']), fn($q) => $q->whereDate("created_at", $filters['created_at']))
            ->when(isset($filters['status']), fn($q) => $q->where('status', $filters['status']));
    }

    public function transformData()
    {
        return [
            'sender' => optional($this->sender)->name ?? 'N/A',
            'receiver' => optional($this->receiver)->name ?? 'N/A',
            'receiverEmail' => $this->email ?? 'N/A',
            'transactionId' => $this->utr ?? 'N/A',
            'amount' => getAmount($this->amount) ?? 'N/A',
            'currency' => optional($this->currency)->code,
            'type' => $this->sender_id == Auth::id() ? 'Sent' : 'Received',
            'status' => $this->status == 1 ? 'Unused' : ($this->status == 2 ? 'Used' : 'Pending'),
            'createdTime' => $this->created_at,
        ];
    }

    public function scopeGetProfit($query, $days = null): Builder
    {
        $baseCurrencyRate = "(SELECT exchange_rate FROM currencies WHERE currencies.id = redeem_codes.currency_id LIMIT 1)";
        $status = 1;
        return $this->addProfitQuery($query, $baseCurrencyRate, $status, $days);
    }

    public function prunable(): Builder
    {
        return static::where('created_at', '<=', now()->subDays(2))->where('status', 0);
    }

}
