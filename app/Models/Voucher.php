<?php

namespace App\Models;

use App\Traits\ProfitQueryTrait;
use App\Traits\RandomCode;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
	use HasFactory,RandomCode,ProfitQueryTrait;

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

	public function depositable()
	{
		return $this->morphOne(Deposit::class, 'depositable');
	}

	public function successDepositable()
	{
		return $this->morphOne(Deposit::class, 'depositable')->where('status', 1);
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

    public function getStatus(): string
    {
        if ($this->status == 0) {
            return '<span class="badge bg-soft-warning text-warning">
                        <span class="legend-indicator bg-warning"></span>' . trans('Pending') . '
                    </span>';
        } elseif ($this->status == 1) {
            return '<span class="badge bg-soft-secondary text-secondary">
                        <span class="legend-indicator bg-secondary"></span>' . trans('Generated') . '
                    </span>';

        } elseif ($this->status == 2) {
            return '<span class="badge bg-soft-success text-success">
                    <span class="legend-indicator bg-success"></span>' . trans('Payment Done') . '
                 </span>';
        } elseif ($this->status == 5) {
            return '<span class="badge bg-soft-danger text-danger">
                    <span class="legend-indicator bg-danger"></span>' . trans('Canceled') . '
                 </span>';
        } else {
            return 'Unknown';
        }
    }



    public function scopeSearch($query,$search)
    {
        $userId = auth()->id();
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
            ->when(isset($search['sender']), function ($q) use ($search) {
                $q->whereHas('sender', function ($qry) use ($search) {
                    $qry->whereRaw("CONCAT(firstname, ' ', lastname) LIKE ?", ["%{$search['sender']}%"])
                        ->orWhere('username', 'LIKE', "%{$search['sender']}%");
                });
            })
            ->when(isset($search['receiver']), function ($q) use ($search) {
                $q->whereHas('receiver', function ($qry) use ($search) {
                    $qry->whereRaw("CONCAT(firstname, ' ', lastname) LIKE ?", ["%{$search['receiver']}%"])
                        ->orWhere('username', 'LIKE', "%{$search['receiver']}%");
                });
            })
            ->when(isset($search['type']) && preg_match("/sent/", $search['type']), function ($query) use ($userId) {
                return $query->where("sender_id", $userId);
            })
            ->when(isset($search['type']) && preg_match("/received/", $search['type']), function ($query) use ($userId) {
                return $query->where("receiver_id", $userId);
            })
            ->when(isset($search['created_at']) && preg_match("/^[0-9]{2,4}-[0-9]{1,2}-[0-9]{1,2}$/", $search['created_at']), function ($query) use ($search) {
                return $query->whereDate("created_at", $search['created_at']);
            })
            ->when(isset($search['status']), function ($query) use ($search) {
                return $query->where('status', $search['status']);
            });
    }

    public function transformData()
    {
        return [
            'senderId' => $this->sender_id,
            'receiverId' => $this->receiver_id,
            'userId' => auth()->id(),
            'sender' => optional($this->sender)->name ?? 'N/A',
            'receiver' => optional($this->receiver)->name ?? 'N/A',
            'receiverEmail' => $this->email,
            'currency' => optional($this->currency)->name,
            'transactionId' => $this->utr,
            'requestedAmount' => getAmount($this->amount),
            'currencyCode' => optional($this->currency)->code,
            'type' => $this->sender_id == auth()->id() ? 'Sent' : 'Received',
            'status' => $this->getStatusLabel(),
            'createdTime' => $this->created_at,
        ];
    }

    public function getStatusLabel()
    {
        return match ($this->status) {
            0 => 'Pending',
            1 => 'Generated',
            2 => 'Payment done',
            5 => 'Canceled',
            default => 'N/A',
        };
    }

    public function scopeGetProfit($query, $days = null): Builder
    {
        $baseCurrencyRate = "(SELECT exchange_rate FROM currencies WHERE currencies.id = vouchers.currency_id LIMIT 1)";
        $status = 1;
        return $this->addProfitQuery($query, $baseCurrencyRate, $status, $days);
    }


}
