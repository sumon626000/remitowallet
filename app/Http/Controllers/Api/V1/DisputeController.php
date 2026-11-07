<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Dispute;
use App\Models\DisputeDetails;
use App\Models\Escrow;
use App\Traits\ApiValidation;
use App\Traits\Notify;
use App\Traits\Upload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class DisputeController extends Controller
{
	use ApiValidation, Upload, Notify;

	public function dispute(Request $request)
	{
		try {
			$user = Auth::user();
			$escrow = Escrow::where('utr', $request->utr)
				->where(function ($query) use ($user) {
					$query->where('sender_id', $user->id)
						->orWhere('receiver_id', $user->id);
				})
				->first();

			if (!$escrow) {
				return response()->json($this->withErrors('Record not found'));
			}
			$data['escrow'] = $escrow;
			$data['dispute'] = Dispute::with(['disputable', 'disputeDetails' => function ($query) use ($user) {
				return $query->where('user_id', $user->id)
					->orWhereNull('user_id')
					->orderBy('id', 'DESC');
			}, 'disputeDetails.user', 'disputeDetails.admin'])->where([
				'disputable_id' => $escrow->id,
				'disputable_type' => Escrow::class,
			])->first();

			return response()->json($this->withSuccess($data));
		} catch (\Exception $e) {
			return response()->json($this->withErrors($e->getMessage()));
		}
	}

	public function disputeSubmit(Request $request)
	{
		try {
			$user = Auth::user();
			$escrow = Escrow::where('utr', $request->utr)->byUser()->first();

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
					return response()->json($this->withErrors('Dispute closed'));
				} elseif (($escrow->sender_id == $user->id && $dispute->defender_reply_yn == 0) || ($escrow->receiver_id == $user->id && $dispute->claimer_reply_yn == 0)) {
					return response()->json($this->withErrors('You are muted, you are unable to sent message'));
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
			$disputeDetails->utr = 'DD';
			$disputeDetails->message = $request->message;

			if ($request->hasFile('attachments')) {
				$path = config('filelocation.dispute.path');
				$files = [];
				foreach ($request->file('attachments') as $image) {
					try {
						$files[] = $this->uploadImage($image, $path);
					} catch (\Exception $exp) {
						return response()->json($this->withErrors('Could not upload your ' . $image));
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
				"link" => $link,
				"icon" => "fa fa-money-bill-alt text-white"
			];
			$firebaseAction = $link;
			$this->adminMail('DISPUTE_REQUEST_TO_ADMIN', $params);
			$this->adminPushNotification('DISPUTE_REQUEST_TO_ADMIN', $params, $action);
			$this->adminFirebasePushNotification('DISPUTE_REQUEST_TO_ADMIN', $params, $firebaseAction);

			return response()->json($this->withSuccess('Successfully'));
		} catch (\Exception $e) {
			return response()->json($this->withErrors($e->getMessage()));
		}
	}


	public function disputeList(Request $request)
	{
		try {
			$array = [];
			$search = $request->all();
			$user = Auth::user();
			$created_date = isset($search['created_at']) ? preg_match("/^[0-9]{2,4}-[0-9]{1,2}-[0-9]{1,2}$/", $search['created_at']) : 0;

			$data['disputes'] = tap(Dispute::with(['disputeDetails'])
				->when(isset($search['utr']), function ($query) use ($search) {
					return $query->where('utr', 'LIKE', "%{$search['utr']}%");
				})
				->when($created_date == 1, function ($query) use ($search) {
					return $query->whereDate("created_at", $search['created_at']);
				})
				->when(isset($search['status']), function ($query) use ($search) {
					return $query->where('status', $search['status']);
				})
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
				->latest()->paginate(20)
                ->through(fn($dispute) => $this->formatDispute($dispute)));

			return response()->json($this->withSuccess($data));
		} catch (\Exception $e) {
			return response()->json($this->withErrors($e->getMessage()));
		}
	}

    private function formatDispute($dispute)
    {
        return [
            'disputeFor' => $dispute->disputable_type === Escrow::class ? 'Escrow' : '-',
            'escrowRoute' => $dispute->disputable_type === Escrow::class
                ? route('api.escrowPreview', optional($dispute->disputable)->utr)
                : '-',
            'disputeId' => $dispute->utr ?? 'N/A',
            'status' => $this->getDisputeStatus($dispute->status),
            'createdTime' => $dispute->created_at
        ];
    }

    private function getDisputeStatus($status)
    {
        return match ($status) {
            0 => 'Open',
            1 => 'Solved',
            2 => 'Closed',
            default => 'Unknown'
        };
    }

}
