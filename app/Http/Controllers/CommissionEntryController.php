<?php

namespace App\Http\Controllers;

use App\Models\CommissionEntry;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class CommissionEntryController extends Controller
{

    public function index(Request $request)
    {
        $userId = Auth::id();
        $search = $request->all();

        $commissionEntries = CommissionEntry::query()
            ->with(['sender', 'receiver', 'currency'])
            ->filterByUser($userId)
            ->search($search)
            ->latest()
            ->paginate()
            ->appends($search);

        return view('user.referralBonus.index', compact('search', 'commissionEntries'));
    }

	public function indexAdmin()
	{
		$commissionEntries = CommissionEntry::with(['sender', 'receiver', 'currency'])->latest()->get();
		return view('admin.referralBonus.index', compact('commissionEntries'));
	}

    public function searchAdmin(Request $request)
    {
        $filters = [
            'search' => $request->search['value'] ?? null,
            'trx_id' => $request->trx_id,
            'filter_one' => $request->filter_one,
            'filter_two' => $request->filter_two,
            'filter_date' => $request->filter_date
        ];

        $dates = explode('-', $filters['filter_date'] ?? '');
        $startDate = isset($dates[0]) ? trim($dates[0]) : null;
        $endDate = isset($dates[1]) ? trim($dates[1]) : null;

        try {
            $startDate = $startDate ? Carbon::createFromFormat('d/m/Y', $startDate) : null;
            $endDate = $endDate ? Carbon::createFromFormat('d/m/Y', $endDate) : null;
        } catch (\Exception $e) {
            $startDate = $endDate = null;
        }

        $commissions = CommissionEntry::query()
            ->with(['sender', 'receiver', 'currency'])
            ->latest()
            ->when($filters['trx_id'], fn($query, $trx) => $query->where('utr', 'LIKE', "%$trx%"))
            ->when($filters['filter_one'], fn($query, $name) => $query->whereHas('sender', fn($q) => $q->where('email', 'LIKE', "%$name%")))
            ->when($filters['filter_two'], fn($query, $name2) => $query->whereHas('receiver', fn($q) => $q->where('email', 'LIKE', "%$name2%")))
            ->when($startDate && !$endDate, fn($query) => $query->whereDate('created_at', $startDate))
            ->when($startDate && $endDate, fn($query) => $query->whereBetween('created_at', [$startDate, $endDate]))
            ->when($filters['search'], function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'LIKE', "%$search%")
                        ->orWhere('utr', 'LIKE', "%$search%")
                        ->orWhereHas('sender', fn($q) =>
                        $q->whereRaw("CONCAT(firstname, ' ', lastname) LIKE ?", ["%$search%"])
                            ->orWhere('username', 'LIKE', "%$search%")
                        );
                });
            });

        return DataTables::of($commissions)
            ->addColumn('sender', function ($item) {
                $url = route("admin.user.edit", $item->from_user);
                return '<a class="d-flex align-items-center me-2" href="' . $url . '">
                            <div class="flex-shrink-0">
                              ' . optional($item->sender)->profilePicture() . '
                            </div>
                            <div class="flex-grow-1 ms-3">
                              <h5 class="text-hover-primary mb-0">' . optional($item->sender)->name. '</h5>
                              <span class="fs-6 text-body">@' . optional($item->sender)->username . '</span>
                            </div>
                        </a>';
            })
            ->addColumn('receiver', function ($item) {
                $url = route("admin.user.edit", $item->to_user);
                return '<a class="d-flex align-items-center me-2" href="' . $url . '">
                            <div class="flex-shrink-0">
                              ' . optional($item->receiver)->profilePicture() . '
                            </div>
                            <div class="flex-grow-1 ms-3">
                              <h5 class="text-hover-primary mb-0">' . optional($item->receiver)->name. '</h5>
                              <span class="fs-6 text-body">@' . optional($item->receiver)->username . '</span>
                            </div>
                        </a>';
            })
            ->addColumn('amount', function ($item) {
                return '<span class="amount-highlight">'.currencyPosition($item->commission_amount).'</span>';
            })
            ->addColumn('level', function ($item) {
                return '<span class="badge bg-soft-dark text-dark">'. $item->level. '</span>';
            })
            ->addColumn('title', function ($item) {
                return $item->title;
            })
            ->addColumn('transaction_id', function ($item) {
                return $item->utr;
            })
            ->addColumn('transaction_at', function ($item) {
                return dateTime($item->created_at);
            })
            ->rawColumns(['sender', 'receiver', 'amount', 'level'])
            ->make(true);
    }


}
