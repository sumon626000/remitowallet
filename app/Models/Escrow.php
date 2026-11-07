<?php

namespace App\Models;

use App\Traits\RandomCode;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Support\Facades\Auth;

class Escrow extends Model
{
    use HasFactory, RandomCode ,Prunable;

    protected $guarded = ['id'];

    public function transactional()
    {
        return $this->morphOne(Transaction::class, 'transactional');
    }

    public function disputable()
    {
        return $this->morphOne(Dispute::class, 'disputable');
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


    public function scopeByUser($query, $userId = null)
    {
        $userId = $userId ?? Auth::id();
        return $query->where(fn($q) => $q->where('sender_id', $userId)->orWhere('receiver_id', $userId));
    }

    public function scopeSearch($query, $filters, $userId)
    {
        return $query
            ->when(isset($filters['email']), fn($q) => $q->where('email', 'LIKE', "%{$filters['email']}%"))
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
            ->when(isset($filters['type']) && preg_match("/sent/", $filters['type']), fn($q) => $q->where('sender_id', $userId))
            ->when(isset($filters['type']) && preg_match("/received/", $filters['type']), fn($q) => $q->where('receiver_id', $userId))
            ->when(isset($filters['created_at']) && preg_match("/^\d{2,4}-\d{1,2}-\d{1,2}$/", $filters['created_at']), fn($q) => $q->whereDate('created_at', $filters['created_at']))
            ->when(isset($filters['status']), fn($q) => $q->where('status', $filters['status']));
    }

    public function getStatus()
    {
        return match ($this->status) {
            0 => 'Pending',
            1 => 'Generated',
            2 => 'Payment Done',
            3 => 'Request for payment',
            4 => 'Payment Disbursed',
            5 => 'Canceled',
            6 => match (optional($this->disputable)->status) {
                0 => ($this->sender_id == Auth::id() ? 'Hold' : 'Dispute'),
                1 => 'Dispute | Refunded',
                2 => 'Dispute | Payment Disbursed',
                default => 'N/A'
            },
            default => 'N/A',
        };
    }

    public function getStatusBadge()
    {
        return match ((int) $this->status) {
            0 => $this->badge('warning', 'Pending'),
            1 => $this->badge('secondary', 'Generated'),
            2 => $this->badge('success', 'Payment Done'),
            3 => $this->badge('success', 'Request for payment'),
            4 => $this->badge('info', 'Payment Disbursed'),
            5 => $this->badge('danger', 'Canceled'),
            6 => $this->disputeBadge(),
            default => $this->badge('warning', 'N/A'),
        };
    }

    protected function badge($color, $label)
    {
        $label = trans($label);
        return <<<HTML
            <span class="badge bg-soft-{$color} text-{$color}">
                <span class="legend-indicator bg-{$color}"></span>{$label}
            </span>
        HTML;
    }

    protected function disputeBadge()
    {
        return match ((int) optional($this->disputable)->status) {
            0 => $this->sender_id == auth()->id()
                ? $this->badge('dark', 'Hold')
                : $this->badge('dark', 'Dispute'),
            1 => $this->badge('dark', 'Dispute | Refunded'),
            2 => $this->badge('dark', 'Dispute | Payment Disbursed'),
            default => $this->badge('dark', 'Dispute'),
        };
    }

    public function prunable(): Builder
    {
        return static::where('created_at', '<=', now()->subDays(2))->where('status', 0);
    }


}
