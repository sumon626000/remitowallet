<?php

namespace App\Mail;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class SendStatementMail extends Mailable
{
	use Queueable, SerializesModels;

	public $transactions;
	public $logo;
	public $date;

	/**
	 * Create a new message instance.
	 *
	 * @return void
	 */
	public function __construct($transactions)
	{
		$this->transactions = $transactions;
		$this->logo = getFile(config('location.logo.path') . 'logo.png');
		$this->date = Carbon::today()->format('d M y');
	}

	/**
	 * Build the message.
	 *
	 * @return $this
	 */
	public function build()
	{
		$mailMessage = $this->subject("Transaction Statement")
			->from(config('basic.sender_email'), config('basic.sender_email_name'))
			->attachData($this->pdf(), 'statement.pdf', [
				'mime' => 'application/pdf',
			]);

		$msg = 'The attachment represent your ' . ucfirst(config('basic.mail_recurring')) . ' transaction statements.';
		$mailMessage->view('layouts.mail')->with('msg', $msg);
	}

	private function pdf()
	{
		$pdf = Pdf::loadView('user.statement-pdf', [
			'logo' => $this->logo,
			'date' => $this->date,
			'transactions' => $this->transactions,
		]);

		return $pdf->stream();
	}
}
