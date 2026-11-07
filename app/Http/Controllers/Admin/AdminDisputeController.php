<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dispute;
use App\Models\DisputeDetails;
use App\Models\Escrow;
use Facades\App\Services\BasicService;
use App\Traits\Notify;
use App\Traits\Upload;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class AdminDisputeController extends Controller
{
    use Upload, Notify;

    public function index()
    {
        $data['disputes'] = collect(Dispute::selectRaw('COUNT(id) AS totalDispute')
            ->selectRaw('COUNT(CASE WHEN status = 1 THEN id END) AS solvedDispute')
            ->selectRaw('(COUNT(CASE WHEN status = 1 THEN id END) / COUNT(id)) * 100 AS solvedDisputePercentage')
            ->selectRaw('COUNT(CASE WHEN status = 0 THEN id END) AS openDispute')
            ->selectRaw('(COUNT(CASE WHEN status = 0 THEN id END) / COUNT(id)) * 100 AS openDisputePercentage')
            ->selectRaw('COUNT(CASE WHEN status = 2 THEN id END) AS closeDispute')
            ->selectRaw('(COUNT(CASE WHEN status = 2 THEN id END) / COUNT(id)) * 100 AS closeDisputePercentage')
            ->selectRaw('COUNT(CASE WHEN DATE(created_at) = CURRENT_DATE THEN id END) AS todayDispute')
            ->selectRaw('(COUNT(CASE WHEN DATE(created_at) = CURRENT_DATE THEN id END) / COUNT(id)) * 100 AS todayDisputePercentage')
            ->get()
            ->toArray())->collapse();

        return view('admin.dispute.index', $data);
    }

    public function search(Request $request)
    {
        $search = $request->search['value'] ?? null;
        $filterName = $request->filter_trx_id;
        $filterStatus = $request->filter_status;
        $filterDate = explode('-', $request->filter_date);
        $startDate = $filterDate[0];
        $endDate = isset($filterDate[1]) ? trim($filterDate[1]) : null;

        $disputes = Dispute::with(['disputable'])->latest()
            ->when(isset($filterName), function ($query) use ($filterName) {
                return $query->where('utr', 'LIKE', '%' . $filterName . '%');
            })
            ->when(isset($filterStatus), function ($query) use ($filterStatus) {
                if ($filterStatus != "all") {
                    return $query->where('status', $filterStatus);
                }
            })
            ->when(!empty($request->filter_date) && $endDate == null, function ($query) use ($startDate) {
                $startDate = Carbon::createFromFormat('d/m/Y', trim($startDate));
                $query->whereDate('created_at', $startDate);
            })
            ->when(!empty($request->filter_date) && $endDate != null, function ($query) use ($startDate, $endDate) {
                $startDate = Carbon::createFromFormat('d/m/Y', trim($startDate));
                $endDate = Carbon::createFromFormat('d/m/Y', trim($endDate));
                $query->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->when(!empty($search), function ($q) use ($search) {
                $q->where(function ($subquery) use ($search) {
                    $subquery->where('utr', 'LIKE', "%{$search}%")
                        ->orWhereHas('disputable.sender', fn($q) =>
                            $q->where('username', 'LIKE', "%$search%")
                        )
                        ->orWhereHas('disputable.receiver', fn($q) =>
                            $q->where('username', 'LIKE', "%$search%")
                        );
                });
            });
        return DataTables::of($disputes)
            ->addColumn('dispute_id', function ($item) {
                return $item->utr;
            })
            ->addColumn('name', function ($item) {
                if ($item->disputable_type == Escrow::class) {
                    return 'Escrow';
                }
            })
            ->addColumn('claimer', function ($item) {
                if (isset($item->disputable->receiver->id)) {
                    $url = route("admin.user.edit", optional($item->disputable->receiver)->id);
                    return '<a class="d-flex align-items-center me-2" href="' . $url . '">
                                <div class="flex-shrink-0">
                                  ' . optional($item->disputable->receiver)->profilePicture() . '
                                </div>
                                <div class="flex-grow-1 ms-3">
                                  <h5 class="text-hover-primary mb-0">' . optional($item->disputable->receiver)->firstname . ' ' . optional($item->disputable->receiver)->lastname . '</h5>
                                  <span class="fs-6 text-body">@' . optional($item->disputable->receiver)->username . '</span>
                                </div>
                              </a>';
                } else {
                    return 'N/A';
                }
            })
            ->addColumn('defender', function ($item) {
                if (isset($item->disputable->sender->id)) {
                    $url = route("admin.user.edit", optional($item->disputable->sender)->id);
                    return '<a class="d-flex align-items-center me-2" href="' . $url . '">
                                <div class="flex-shrink-0">
                                  ' . optional($item->disputable->sender)->profilePicture() . '
                                </div>
                                <div class="flex-grow-1 ms-3">
                                  <h5 class="text-hover-primary mb-0">' . optional($item->disputable->sender)->firstname . ' ' . optional($item->disputable->sender)->lastname . '</h5>
                                  <span class="fs-6 text-body">@' . optional($item->disputable->sender)->username . '</span>
                                </div>
                              </a>';
                } else {
                    return 'N/A';
                }
            })
            ->addColumn('status', function ($item) {
                if ($item->status == 1) {
                    return '<span class="badge bg-soft-success text-success">
                    <span class="legend-indicator bg-success"></span>' . trans('Solved') . '
                  </span>';

                } elseif ($item->status == 0) {
                    return '<span class="badge bg-soft-primary text-primary">
                    <span class="legend-indicator bg-primary"></span>' . trans('Open') . '
                  </span>';
                } else {
                    return '<span class="badge bg-soft-danger text-danger">
                    <span class="legend-indicator bg-danger"></span>' . trans('Closed') . '
                  </span>';
                }
            })
            ->addColumn('created_at', function ($item) {
                return dateTime($item->created_at);
            })
            ->addColumn('action', function ($item) {
                $viewRoute = route('admin.dispute.view', $item->utr);

                return "<div class='btn-group' role='group'>
                      <a href='" . $viewRoute . "' class='btn btn-white btn-sm'>
                        <i class='bi-eye me-1'></i> " . trans('View') . "
                      </a>";

            })
            ->rawColumns(['dispute_id', 'name', 'claimer', 'defender', 'status', 'created_at', 'action'])
            ->make(true);
    }

    public function disputeStatusChange(Request $request, $utr, $option)
    {
        $dispute = Dispute::with('disputable', 'disputable.receiver', 'disputable.sender')
            ->where('utr', $utr)->first();
        $disputable = $dispute->disputable;
        $status = $dispute->status;
        if ($status == 1) {
            return back('error', 'Issue already solved');
        } elseif ($status == 2) {
            return back('error', 'Issue already closed');
        }
        $dispute->status = $option;
        $action = 0;
        $message = '';

        if ($option == 1) {
            /*
             * refund money to claimer
             * */
            updateWallet($disputable->receiver_id, $disputable->currency_id, $disputable->transfer_amount, 1);
            $action = 0;
            $remark = 'Balance credited from disputed';
            BasicService::makeTransaction($disputable->receiver, $disputable->currency_id, $disputable->transfer_amount, $disputable->charge,
                '+', $dispute->utr, $remark, $dispute->id, Dispute::class);
        } elseif ($option == 2) {
            /* add money to defender Wallet */
            updateWallet(optional($dispute->disputable)->sender_id, optional($dispute->disputable)->currency_id, optional($dispute->disputable)->received_amount, 1);
            $message = 'Issue marked as closed successfully';
            $action = 1;
            $remark = 'Balance credited from disputed';
            BasicService::makeTransaction($disputable->sender, $disputable->currency_id, $disputable->transfer_amount, $disputable->charge,
                '+', $dispute->utr, $remark, $dispute->id, Dispute::class);
        }
        $dispute->save();

        $disputeDetails = new DisputeDetails();
        $disputeDetails->utr = (string)Str::uuid();
        $disputeDetails->action = $action;
        $disputeDetails->dispute_id = $dispute->id;
        $disputeDetails->admin_id = Auth::id();
        $disputeDetails->status = 1;
        $disputeDetails->save();
        return back()->with('success', __($message));
    }

    public function claimerMuteUnmute(Request $request, $utr, $option)
    {
        $dispute = Dispute::with('disputable')->where('utr', $utr)->first();

        $dispute->claimer_reply_yn = !$dispute->claimer_reply_yn;
        $dispute->save();

        if ($dispute->claimer_reply_yn == 0) {
            $message = 'Claimer has been muted successfully';
            $action = 2;
        } else {
            $message = 'Claimer has been unmuted successfully';
            $action = 3;
        }
        $disputeDetails = new DisputeDetails();
        $disputeDetails->utr = (string)Str::uuid();
        $disputeDetails->action = $action;
        $disputeDetails->dispute_id = $dispute->id;
        $disputeDetails->admin_id = Auth::id();
        $disputeDetails->status = 1;
        $disputeDetails->user_id = optional($dispute->disputable)->receiver_id;
        $disputeDetails->save();
        return back()->with('success', __($message));
    }

    public function defenderMuteUnmute(Request $request, $utr, $option)
    {
        $dispute = Dispute::with('disputable')->where('utr', $utr)->first();

        $dispute->defender_reply_yn = !$dispute->defender_reply_yn;
        $dispute->save();

        if ($dispute->defender_reply_yn == 0) {
            $message = 'Defender has been muted successfully';
            $action = 2;
        } else {
            $message = 'Defender has been unmuted successfully';
            $action = 3;
        }
        $disputeDetails = new DisputeDetails();
        $disputeDetails->utr = (string)Str::uuid();
        $disputeDetails->action = $action;
        $disputeDetails->dispute_id = $dispute->id;
        $disputeDetails->admin_id = Auth::id();
        $disputeDetails->status = 1;
        $disputeDetails->user_id = optional($dispute->disputable)->sender_id;
        $disputeDetails->save();
        return back()->with('success', __($message));
    }

    public function adminDisputeView(Request $request, $utr)
    {
        $dispute = Dispute::with(['disputable', 'disputeDetails' => function ($query) {
            $query->orderBy('id', 'DESC');
        }, 'disputeDetails.user', 'disputeDetails.admin'])
            ->where('utr', $utr)
            ->firstOrFail();

        if ($request->isMethod('get')) {
            return view('admin.dispute.view', compact('dispute'));
        } elseif ($request->isMethod('put')) {
            $images = $request->file('attachments');
            $allowedExtension = array('jpg', 'png', 'jpeg', 'pdf');

            $this->validate($request, [
                'attachments.*' => [
                    'max:102400',
                    function ($attribute, $value, $fail) use ($images, $allowedExtension) {
                        foreach ($images as $img) {
                            $ext = strtolower($img->getClientOriginalExtension());
                            if (($img->getSize() / 1000000) > 2) {
                                return $fail("Images maximum 100 MB allowed.");
                            }
                            if (!in_array($ext, $allowedExtension)) {
                                return $fail("Only png, jpg, jpeg, pdf images are allowed");
                            }
                        }
                        if (count($images) > 5) {
                            return $fail("Maximum 5 images can be uploaded");
                        }
                    },
                ],
                'message' => 'required'
            ]);

            $defender_id = optional($dispute->disputable)->sender_id;

            if ($request->user_id == $defender_id || !isset($request->user_id)) {
                $checkDisputeDetails = DisputeDetails::where('dispute_id', $dispute->id)->whereNotNull('message')
                    ->where(function ($query) use ($defender_id) {
                        $query->where('user_id', $defender_id)->orWhereNull('user_id');
                    })->count();
                $dispute->defender_reply_yn = $checkDisputeDetails > 0 ? $dispute->defender_reply_yn : 1;
            }

            $dispute->save();
            $user = Auth::user();
            $disputeDetails = new DisputeDetails();
            $disputeDetails->dispute_id = $dispute->id;
            $disputeDetails->admin_id = $user->id;
            $disputeDetails->user_id = $request->user_id;
            $disputeDetails->status = 1;
            $disputeDetails->utr = (string)Str::uuid();
            $disputeDetails->message = $request->message;
            if ($request->hasFile('attachments')) {
                $files = [];
                foreach ($request->file('attachments') as $image) {
                    try {
                        $uploadedImage = $this->fileUpload($image, config('filelocation.dispute.path'), null, null, 'webp', 60);
                        $files[] = [
                            'file' => $uploadedImage['path'],
                            'driver' => $uploadedImage['driver'],
                        ];
                    } catch (\Exception $exp) {
                        return back()->withInput()->with('error', 'Could not upload your ' . $image);
                    }
                }
                $disputeDetails->files = $files;
            }
            $disputeDetails->save();

            // Mail and push notification to USER
            $receivedUser = $disputeDetails->user;
            $link = route('user.dispute.view', $dispute->utr);
            $params = [
                'sender' => $user->fullname,
                'transaction' => $dispute->utr,
                'link' => $link,
            ];
            $action = [
                "link" => $link,
                "icon" => "fa fa-money-bill-alt text-white"
            ];
            $firebaseAction = $link;
            $this->sendMailSms($receivedUser, 'DISPUTE_REQUEST_TO_USER', $params);
            $this->userPushNotification($receivedUser, 'DISPUTE_REQUEST_TO_USER', $params, $action);
            $this->userFirebasePushNotification($receivedUser, 'DISPUTE_REQUEST_TO_USER', $params, $firebaseAction);
            return back();
        }
    }

}
