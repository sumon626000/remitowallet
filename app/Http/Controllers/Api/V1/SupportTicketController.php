<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket as Ticket;
use App\Models\SupportTicketAttachment as TicketAttachment;
use App\Models\SupportTicketMessage as TicketMessage;
use App\Models\User;
use App\Traits\ApiValidation;
use App\Traits\Notify;
use App\Traits\Upload;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Facades\App\Http\Controllers\User\SupportTicketController as SupportController;
use Stevebauman\Purify\Facades\Purify;

class SupportTicketController extends Controller
{
	use ApiValidation, Notify, Upload;

	public function ticketList()
	{
		if (auth()->id() == null) {
			return response()->json($this->withErrors('Something went wrong'));
		}
		try {
			$array = [];
			$tickets = tap(Ticket::where('user_id', auth()->id())->latest()
				->paginate(20), function ($paginatedInstance) use ($array) {
				return $paginatedInstance->getCollection()->transform(function ($query) use ($array) {
					$array['ticket'] = $query->ticket;
					$array['subject'] = ucfirst($query->subject);
					if ($query->status == 0) {
						$array['status'] = trans('Open');
					} elseif ($query->status == 1) {
						$array['status'] = trans('Answered');
					} elseif ($query->status == 2) {
						$array['status'] = trans('Replied');
					} elseif ($query->status == 3) {
						$array['status'] = trans('Closed');
					}
					$array['lastReply'] = diffForHumans($query->last_reply);
					return $array;
				});
			});

			if ($tickets) {
				return response()->json($this->withSuccess($tickets));
			} else {
				return response()->json($this->withErrors('No data found'));
			}
		} catch (\Exception $e) {
			return response()->json($this->withErrors($e));
		}
	}

	public function ticketCreate(Request $request)
	{
		try {

			$this->newTicketValidation($request);
			$random = rand(100000, 999999);

			$ticket = SupportController::saveTicket($request, $random);

			$message = SupportController::saveMsgTicket($request, $ticket);

            if (!empty($request->attachments)) {
                $numberOfAttachments = count($request->attachments);
                for ($i = 0; $i < $numberOfAttachments; $i++) {
                    if ($request->hasFile('attachments.' . $i)) {
                        $file = $request->file('attachments.' . $i);
                        $supportFile = $this->fileUpload($file, config('filelocation.ticket.path'));
                        if (empty($supportFile['path'])) {
                            throw new \Exception('File could not be uploaded.');
                        }
                        SupportController::saveAttachment($message, $supportFile['path'], $supportFile['driver']);
                    }
                }
            }

			$this->ticketCreateNotify($ticket);
			return response()->json($this->withSuccess('Your Ticket has been pending'));

		} catch (\Exception $e) {
			return response()->json($this->withErrors($e->getMessage()));
		}
	}

	public function newTicketValidation(Request $request)
	{
		$imgs = $request->file('attachments');
		$allowedExts = array('jpg', 'png', 'jpeg', 'pdf');


		$this->validate($request, [
			'attachments' => [
				'max:4096',
				function ($attribute, $value, $fail) use ($imgs, $allowedExts) {
					foreach ($imgs as $img) {
						$ext = strtolower($img->getClientOriginalExtension());
						if (($img->getSize() / 1000000) > 2) {
							return response()->json($this->withErrors('Images MAX  2MB ALLOW!'));
						}

						if (!in_array($ext, $allowedExts)) {
							return response()->json($this->withErrors('Only png, jpg, jpeg, pdf images are allowed'));
						}
					}
					if (count($imgs) > 5) {
						return response()->json($this->withErrors('Maximum 5 images can be uploaded'));
					}
				}
			],
			'subject' => 'required|max:100',
			'message' => 'required'
		]);
	}

	public function ticketCreateNotify($ticket)
	{
		try {
			$msg = [
				'username' => optional($ticket->user)->username,
				'ticket_id' => $ticket->ticket
			];
			$action = [
				"link" => route('admin.ticket.view', $ticket->id),
				"icon" => "fas fa-ticket-alt text-white"
			];

			$this->adminPushNotification('SUPPORT_TICKET_CREATE', $msg, $action);
			$firebaseAction = route('admin.ticket.view', $ticket->id);
			$this->adminFirebasePushNotification('SUPPORT_TICKET_CREATE', $msg, $firebaseAction);
			return true;

		} catch (\Exception $e) {
			return true;
		}
	}

	public function ticketView($ticketId)
	{
		try {
			$ticket = Ticket::with('messages')->where('ticket', $ticketId)->latest()->with('messages')->first();
			if (!$ticket) {
				return response()->json($this->withErrors('Something went wrong'));
			}
			$user = User::where('id', auth()->id())->first();
			if (!$user) {
				return response()->json($this->withErrors('User Not Found'));
			}

			$data['id'] = $ticket->id;
			$data['page_title'] = "Ticket: #" . $ticketId . ' ' . $ticket->subject;
			$data['userImage'] = $ticket->user?->getImage();
			$data['userUsername'] = optional($ticket->user)->username;
			if ($ticket->status == 0) {
				$data['status'] = trans('Open');
			} elseif ($ticket->status == 1) {
				$data['status'] = trans('Answered');
			} elseif ($ticket->status == 2) {
				$data['status'] = trans('Replied');
			} elseif ($ticket->status == 3) {
				$data['status'] = trans('Closed');
			}

			if ($ticket->messages) {
				foreach ($ticket->messages as $key => $message) {
					$data['messages'][$key] = $message;
					$data['messages'][$key]['adminImage'] = ($message->admin_id != null ? $message->admin->profilePicture() : null);

					$data['messages'][$key]['attachments'] = collect($message->attachments)->map(function ($attach, $key) {
						$attach->attachment_path = route('api.ticket.download', $attach->id);
						$attach->attachment_name = trans('File') . ' ' . ($key + 1);
					});
				}
			}

			return response()->json($this->withSuccess($data));
		} catch (\Exception $e) {
			return response()->json($this->withErrors($e->getMessage()));
		}
	}

	public function ticketDownlaod($ticket_id)
	{
		$attachment = TicketAttachment::find($ticket_id);
		$file = $attachment->image;
		$full_path = getFile($attachment->driver, $file);

		if (file_exists($full_path)) {
			$title = slug($attachment->supportMessage->ticket->subject);
			$ext = pathinfo($file, PATHINFO_EXTENSION);
			$mimetype = mime_content_type($full_path);
			header('Content-Disposition: attachment; filename="' . $title . '.' . $ext . '";');
			header("Content-Type: " . $mimetype);
			return readfile($full_path);
		}
		return response()->json($this->withErrors('404'));
	}

	public function ticketReply(Request $request)
	{
		$ticket = Ticket::where('ticket',$request->id)->first();

		if (!$ticket) {
			return response()->json($this->withErrors('No data found'));
		}
		if ($request->message == null) {
			return response()->json($this->withErrors('Message Field is required'));
		}

		try {
			$message = new TicketMessage();

			if ($request->replayTicket == 1) {
				$purifiedData = Purify::clean($request->except('_token', '_method'));
				$imgs = $request->file('attachments');
				$allowedExts = array('jpg', 'png', 'jpeg', 'pdf');

				$this->validate($request, [
					'attachments' => [
						'max:4096',
						function ($attribute, $value, $fail) use ($imgs, $allowedExts) {
							foreach ($imgs as $img) {
								$ext = strtolower($img->getClientOriginalExtension());
								if (($img->getSize() / 1000000) > 2) {
									return response()->json($this->withErrors('Images MAX  2MB ALLOW!'));
								}

								if (!in_array($ext, $allowedExts)) {
									return response()->json($this->withErrors('Only png, jpg, jpeg, pdf images are allowed'));
								}
							}
							if (count($imgs) > 5) {
								return response()->json($this->withErrors('Maximum 5 images can be uploaded'));
							}
						}
					],
					'message' => 'required',
				]);

				$ticket->status = 2;
				$ticket->last_reply = Carbon::now();
				$ticket->save();

				$message->support_ticket_id = $ticket->id;
				$message->message = $purifiedData['message'] ?? null;
				$message->save();


                if (!empty($request->attachments)) {
                    $numberOfAttachments = count($request->attachments);
                    for ($i = 0; $i < $numberOfAttachments; $i++) {
                        if ($request->hasFile('attachments.' . $i)) {
                            $file = $request->file('attachments.' . $i);
                            $supportFile = $this->fileUpload($file, config('filelocation.ticket.path'));
                            if (empty($supportFile['path'])) {
                                throw new \Exception('File could not be uploaded.');
                            }
                            SupportController::saveAttachment($message, $supportFile['path'], $supportFile['driver']);
                        }
                    }
                }

				$msg = [
					'username' => optional($ticket->user)->username,
					'ticket_id' => $ticket->ticket
				];
				$action = [
					"link" => route('admin.ticket.view', $ticket->id),
					"icon" => "fas fa-ticket-alt text-white"
				];

				$this->adminPushNotification('SUPPORT_TICKET_REPLIED', $msg, $action);
				$firebaseAction = route('admin.ticket.view', $ticket->id);
				$this->adminFirebasePushNotification('SUPPORT_TICKET_REPLIED', $msg, $firebaseAction);

				return response()->json($this->withSuccess('Ticket has been replied'));

			} elseif ($request->replayTicket == 2) {
				$ticket->status = 3;
				$ticket->last_reply = Carbon::now();
				$ticket->save();

				return response()->json($this->withSuccess('Ticket has been closed'));
			}
			return response()->json($this->withErrors('Something went wrong'));
		} catch (\Exception $e) {
			return response()->json($this->withErrors($e->getMessage()));
		}
	}
}
