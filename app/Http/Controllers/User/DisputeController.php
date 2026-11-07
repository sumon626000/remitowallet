<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Dispute;
use App\Models\DisputeDetails;
use App\Models\Escrow;
use App\Traits\Notify;
use App\Traits\Upload;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class DisputeController extends Controller
{
    use Upload, Notify;

    public function __construct()
    {
        $this->middleware(['auth']);
        $this->middleware(function ($request, $next) {
            $this->user = auth()->user();
            return $next($request);
        });
        $this->theme = template();
    }

    public function index()
    {
        $user = Auth::user();

        $data['disputes'] = collect(Dispute::selectRaw('COUNT(id) AS totalDispute')
            ->selectRaw('COUNT(CASE WHEN status = 1 THEN id END) AS solvedDispute')
            ->selectRaw('(COUNT(CASE WHEN status = 1 THEN id END) / COUNT(id)) * 100 AS solvedDisputePercentage')
            ->selectRaw('COUNT(CASE WHEN status = 0 THEN id END) AS openDispute')
            ->selectRaw('(COUNT(CASE WHEN status = 0 THEN id END) / COUNT(id)) * 100 AS openDisputePercentage')
            ->selectRaw('COUNT(CASE WHEN status = 2 THEN id END) AS closeDispute')
            ->selectRaw('(COUNT(CASE WHEN status = 2 THEN id END) / COUNT(id)) * 100 AS closeDisputePercentage')
            ->selectRaw('COUNT(CASE WHEN DATE(created_at) = CURRENT_DATE THEN id END) AS todayDispute')
            ->selectRaw('(COUNT(CASE WHEN DATE(created_at) = CURRENT_DATE THEN id END) / COUNT(id)) * 100 AS todayDisputePercentage')
            ->whereHasMorph('disputable',
                [Escrow::class],
                function ($query) use ($user) {
                    $query->where('receiver_id', $user->id)
                        ->orWhere(function ($query) use ($user) {
                            $query->where('sender_id', $user->id)
                                ->whereHas('disputable.disputeDetails', function ($query) use ($user) {
                                    $query->whereNull('user_id')
                                        ->orWhere('user_id', $user->id);
                                });
                        });
                })
            ->get()
            ->toArray())->collapse();
        return view('user.dispute.index', $data);
    }

    public function search(Request $request)
    {
        $user = Auth::user();
        $search = $request->search['value'] ?? null;
        $filterName = $request->filter_trx_id;
        $filterStatus = $request->filter_status;
        $filterDate = explode('-', $request->filter_date);
        $startDate = $filterDate[0];
        $endDate = isset($filterDate[1]) ? trim($filterDate[1]) : null;

        $disputes = Dispute::with(['disputeDetails'])
            ->whereHasMorph('disputable',
                [Escrow::class],
                function ($query) use ($user) {
                    $query->where('receiver_id', $user->id)
                        ->orWhere(function ($query) use ($user) {
                            $query->where('sender_id', $user->id)
                                ->whereHas('disputable.disputeDetails', function ($query) use ($user) {
                                    $query->whereNull('user_id')
                                        ->orWhere('user_id', $user->id);
                                });
                        });
                })->latest()
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
            ->when(!empty($search), function ($query) use ($search) {
                return $query->where(function ($subquery) use ($search) {
                    $subquery->where('utr', 'LIKE', "%{$search}%")
                        ->orWhereHas('disputable.sender', function ($q) use ($search) {
                            $q->where('firstname', 'LIKE', "%$search%")
                                ->orWhere('lastname', 'LIKE', "%$search%")
                                ->orWhere('username', 'LIKE', "%$search%");
                        })
                        ->orWhereHas('disputable.receiver', function ($q) use ($search) {
                            $q->where('firstname', 'LIKE', "%$search%")
                                ->orWhere('lastname', 'LIKE', "%$search%")
                                ->orWhere('username', 'LIKE', "%$search%");
                        });
                });
            });
        return DataTables::of($disputes)
            ->addColumn('dispute_id', function ($item) {
                return $item->utr;
            })

            ->addColumn('defender', function ($item) {
                if (isset($item->disputable?->sender?->id)) {
                    return
                    '<a class="d-flex align-items-center me-2" href="#">
                        <div class="flex-shrink-0">
                          ' . $item->disputable?->sender?->profilePicture() . '
                        </div>
                        <div class="flex-grow-1 ms-3">
                          <h5 class="text-hover-primary mb-0">' . $item->disputable?->sender?->name . '</h5>
                          <span class="fs-6 text-body">@' . $item->disputable?->sender?->username . '</span>
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
                return dateTime($item->created_at, basicControl()->date_time_format);
            })
            ->addColumn('action', function ($item) {
                $viewRoute = route('user.dispute.view', optional($item->disputable)->utr);
                $details = route('user.escrow.paymentView', optional($item->disputable)->utr);
                return '
                <div class="btn-group" role="group">
                    <a href="' . $viewRoute . '" class="btn btn-white btn-sm" >
                        <i class="bi-chat-dots me-1"></i> ' . trans("Conversation") . '
                    </a>
                    <div class="btn-group">
                        <button type="button" class="btn btn-white btn-icon btn-sm dropdown-toggle dropdown-toggle-empty" id="userEditDropdown" data-bs-toggle="dropdown" aria-expanded="false"></button>
                        <div class="dropdown-menu dropdown-menu-end mt-1" aria-labelledby="userEditDropdown">
                           <a class="dropdown-item" href="'.$details.'">
                                <i class="bi-eye dropdown-item-icon"></i> ' . trans("Payment Details") . '
                            </a>
                        </div>
                    </div>
                </div>';
            })
            ->rawColumns(['dispute_id',  'defender', 'status', 'created_at', 'action'])
            ->make(true);
    }

    public function userDisputeView(Request $request, $utr)
    {
        $user = Auth::user();
        $escrow = Escrow::where('utr', $utr)
            ->where(function ($query) use ($user) {
                $query->where('sender_id', $user->id)
                    ->orWhere('receiver_id', $user->id);
            })
            ->firstOrFail();

        if ($request->isMethod('get')) {

            $dispute = Dispute::with(['disputable', 'disputeDetails' => function ($query) use ($user) {
                return $query->where('user_id', $user->id)
                    ->orWhereNull('user_id')
                    ->orderBy('id', 'DESC');
            }, 'disputeDetails.user', 'disputeDetails.admin'])->where([
                'disputable_id' => $escrow->id,
                'disputable_type' => Escrow::class,
            ])->first();

            return view('user.dispute.view', compact('escrow', 'dispute'));
        } elseif ($request->isMethod('put')) {
            $images = $request->file('attachments');
            $allowedExtension = ['jpg', 'png', 'jpeg', 'pdf'];
            $this->validate($request, [
                'attachments' => [
                    'max:4096',
                    function ($attribute, $value, $fail) use ($images, $allowedExtension) {
                        foreach ($images as $img) {
                            $ext = strtolower($img->getClientOriginalExtension());
                            if (($img->getSize() / 1000000) > 2) {
                                return $fail("Images MAX  2MB ALLOW!");
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

            $dispute = Dispute::where([
                'disputable_id' => $escrow->id,
                'disputable_type' => Escrow::class,
            ])->first();

            if ($dispute) {
                if ($dispute->status != 0) {
                    return back()->with('error', 'Dispute closed');
                } elseif (($escrow->sender_id == $user->id && $dispute->defender_reply_yn == 0) || ($escrow->receiver_id == $user->id && $dispute->claimer_reply_yn == 0)) {
                    return back()->with('error', 'You are muted, you are unable to sent message');
                }
            } else {
                $dispute = new Dispute();
                $dispute->status = 0; //open
                $dispute->defender_reply_yn = 0;
                $dispute->claimer_reply_yn = 1;
                $dispute->utr = 'D';
                $escrow->disputable()->save($dispute);
                $escrow->status = 6; //0=Pending, 1=generated, 2 = payment done, 3 = sender request to payment disburse, 4 = payment disbursed,5 = cancel, 6= dispute
                $escrow->save();
            }

            $disputeDetails = new DisputeDetails();
            $disputeDetails->dispute_id = $dispute->id;
            $disputeDetails->user_id = $user->id;
            $disputeDetails->status = 0;
            $disputeDetails->utr = 'D';
            $disputeDetails->message = $request->message;

            if ($request->hasFile('attachments')) {
                $path = config('filelocation.dispute.path');
                $files = [];
                foreach ($request->file('attachments') as $image) {
                    try {
                        $image = $this->fileUpload($image, $path);
                        $files[] = [
                            'file' => $image['path'],
                            'driver' => $image['driver']
                        ];
                    } catch (\Exception $exp) {
                        return back()->withInput()->with('alert', 'Could not upload your ' . $image);
                    }
                }
                $disputeDetails->files = $files;
            }
            $disputeDetails->save();

            // Mail and push notification to ADMIN
            $link = route('admin.dispute.view', $dispute->utr);
            $params = [
                'sender' => $user->name,
                'amount' => $escrow->amount,
                'currency' => optional($escrow->currency)->code,
                'transaction' => $escrow->utr,
                'link' => $link,
            ];
            $action = [
                "name" => $user->fullname,
                "image" => getFile($user->image_driver, $user->image),
                "link" => $link,
                "icon" => "fa-light fa-bell-on text-white"
            ];
            $firebaseAction = $link;
            $this->adminMail('DISPUTE_REQUEST_TO_ADMIN', $params);
            $this->adminPushNotification('DISPUTE_REQUEST_TO_ADMIN', $params, $action);
            $this->adminFirebasePushNotification('DISPUTE_REQUEST_TO_ADMIN', $params, $firebaseAction);
            return back();
        }
    }

    public function userDisputeDownload($utr, $file)
    {
        $user = Auth::user();
        $file = decrypt($file);
        DisputeDetails::where('utr', $utr)->where(function ($query) use ($user) {
            $query->where('user_id', $user->id)->orWhereNull('user_id');
        })->whereJsonContains('files', $file)->firstOrFail();
        $path = config('location.dispute.path');
        $full_path = $path . '/' . $file;
        $mimetype = mime_content_type($full_path);
        header("Content-Disposition: attachment; filename= $file");
        header("Content-Type: " . $mimetype);
        return readfile($full_path);
    }
}
