<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommissionEntry extends Model
{
	use HasFactory;

    protected $guarded = ['id'];

	public function sender()
	{
		return $this->belongsTo(User::class, 'from_user', 'id');
	}

	public function receiver()
	{
		return $this->belongsTo(User::class, 'to_user', 'id');
	}

	public function currency()
	{
		return $this->belongsTo(Currency::class, 'currency_id', 'id');
	}

	public function transactional()
	{
		return $this->morphOne(Transaction::class, 'transactional');
	}

    public function scopeFilterByUser($query, $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('to_user', $userId);
        });
    }

    public function scopeSearch($query, $search)
    {
        return $query
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
            ->when(isset($search['utr']), function ($q) use ($search) {
                $q->where('utr', 'LIKE', "%{$search['utr']}%");
            })
            ->when(isset($search['min']), function ($q) use ($search) {
                $q->where('commission_amount', '>=', $search['min']);
            })
            ->when(isset($search['max']), function ($q) use ($search) {
                $q->where('commission_amount', '<=', $search['max']);
            })
            ->when(isset($search['created_at']) && preg_match("/^\d{4}-\d{2}-\d{2}$/", $search['created_at']), function ($q) use ($search) {
                $q->whereDate("created_at", $search['created_at']);
            });
    }

    public function transformData()
    {
        return [
            'sender'       => optional($this->sender)->name ?? 'N/A',
            'receiver'     => optional($this->receiver)->name ?? 'N/A',
            'amount'       => currencyPosition($this->commission_amount) ?? 'N/A',
            'level'        => $this->level ?? 'N/A',
            'title'        => $this->title ?? 'N/A',
            'transactionId' => $this->utr ?? 'N/A',
            'createdAt'    => $this->created_at ?? 'N/A',
        ];
    }

}
