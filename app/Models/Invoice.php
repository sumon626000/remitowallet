<?php

namespace App\Models;

use App\Traits\ProfitQueryTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
	use HasFactory,ProfitQueryTrait;

	protected $guarded = ['id'];

	public function items()
	{
		return $this->morphMany(LineItem::class, 'line_item', 'line_item_type', 'line_item_id');
	}

	public function recuring_invoice()
	{
		return $this->belongsTo(RecuringInvoice::class, 'recuring_invoice_id', 'id');
	}

	public function sendBy()
	{
		return $this->belongsTo(User::class, 'sender_id', 'id');
	}

	public function sender()
	{
		return $this->belongsTo(User::class, 'sender_id', 'id');
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

	public function transactional()
	{
		return $this->morphOne(Transaction::class, 'transactional');
	}

    public function getStatus(): string
    {
        if ($this->status == null) {
            return '<span class="badge bg-soft-warning text-warning">
                        <span class="legend-indicator bg-warning"></span>' . trans('Unpaid') . '
                    </span>';

        } elseif ($this->status == 'paid') {
            return '<span class="badge bg-soft-success text-success">
                    <span class="legend-indicator bg-success"></span>' . trans('Paid') . '
                 </span>';
        } elseif ($this->status == 'rejected') {
            return '<span class="badge bg-soft-danger text-danger">
                    <span class="legend-indicator bg-danger"></span>' . trans('Rejected') . '
                 </span>';
        } else {
            return 'Unknown';
        }
    }

    public function scopeFilter($query, $filters)
    {
        $created_date = isset($filters['created_at']) && preg_match("/^[0-9]{2,4}-[0-9]{1,2}-[0-9]{1,2}$/", $filters['created_at']);

        return $query
            ->when(isset($filters['email']), fn($q) => $q->where('customer_email', 'LIKE', "%{$filters['email']}%"))
            ->when(isset($filters['hash_slug']), fn($q) => $q->where('has_slug', 'LIKE', "%{$filters['hash_slug']}%"))
            ->when(isset($filters['min']), fn($q) => $q->where('grand_total', '>=', $filters['min']))
            ->when(isset($filters['max']), fn($q) => $q->where('grand_total', '<=', $filters['max']))
            ->when(isset($filters['currency_id']) && $filters['currency_id'] !== 'all', fn($q) => $q->where('currency_id', $filters['currency_id']))
            ->when($created_date, fn($q) => $q->whereDate('created_at', $filters['created_at']))
            ->when(isset($filters['status']) && $filters['status'] === 'paid', fn($q) => $q->where('status', 'paid'))
            ->when(isset($filters['status']) && $filters['status'] === 'unpaid', fn($q) => $q->whereNull('status'))
            ->when(isset($filters['status']) && $filters['status'] === 'rejected', fn($q) => $q->where('status', 'rejected'));
    }


    public function transformData()
    {
        return [
            'id' => $this->id,
            'sender' => optional($this->sender)->name ?? 'N/A',
            'receiver' => optional($this->receiver)->name ?? 'N/A',
            'receiverEmail' => $this->customer_email,
            'currency' => optional($this->currency)->name ?? 'N/A',
            'transactionId' => $this->has_slug,
            'requestedAmount' => getAmount($this->grand_total),
            'currencyCode' => optional($this->currency)->code,
            'type' => $this->sender_id == auth()->id() ? 'Sent' : 'Received',
            'status' => $this->status ?? 'Unpaid',
            'createdTime' => $this->created_at
        ];
    }

    public function scopeGetProfit($query, $days = null): Builder
    {
        $baseCurrencyRate = "(SELECT exchange_rate FROM currencies WHERE currencies.id = invoices.currency_id LIMIT 1)";
        $status = 'paid';
        return $this->addProfitQuery($query, $baseCurrencyRate, $status, $days);
    }

}
