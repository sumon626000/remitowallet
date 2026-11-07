<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use PDF;

class SendInvoiceMail extends Mailable
{
	use Queueable, SerializesModels;

	/**
	 * Create a new message instance.
	 *
	 * @return void
	 */
	public $invoice;
	public $sender;

	public function __construct($invoice)
	{
		$this->invoice = $invoice;
		$this->sender = $invoice->sendBy;
	}

	/**
	 * Build the message.
	 *
	 * @return $this
	 */
	public function build()
	{
		$mailMessage = $this->subject(__(optional(auth()->user())->username) . " send payment request for invoice #{$this->invoice->invoice_number}")
			->from(config('basic.sender_email'), config('basic.sender_email_name'))
			->attachData($this->pdf(), $this->invoice->invoice_number . '_Invoice.pdf', [
				'mime' => 'application/pdf',
			]);

		$mailMessage->view('user.invoice.invoiceMail.mail', [
			'invoice_number' => $this->invoice->invoice_number,
			'email' => $this->sender->email,
			'phone' => $this->sender->mobile,
			'currency' => optional($this->invoice->currency)->symbol ?? null,
			'customer_email' => $this->invoice->customer_email,
			'payment' => $this->invoice->frequency,
			'due_date' => $this->invoice->due_date,
			'first_pay_date' => optional($this->invoice->recuring_invoice)->first_arrival_date ?? '',
			'items' => $this->invoice->items,
			'subtotal' => $this->invoice->subtotal,
			'tax' => $this->invoice->tax,
			'taxRate' => $this->invoice->tax_rate,
			'vat' => $this->invoice->vat,
			'vatRate' => $this->invoice->vat_rate,
			'grandTotal' => $this->invoice->grand_total,
			'note' => $this->invoice->note ?? null,
			'url' => route('public.invoice.show', $this->invoice->has_slug),
			'reject_url' => route('reject.invoice.from.email', $this->invoice->has_slug)
		]);
	}

	private function pdf()
	{
		$pdf = PDF::loadView('user.invoice.invoicePdf.pdf', [
			'invoice_number' => $this->invoice->invoice_number,
			'email' => $this->sender->email,
			'phone' => $this->sender->mobile,
			'currency' => optional($this->invoice->currency)->symbol ?? null,
			'customer_email' => $this->invoice->customer_email,
			'payment' => $this->invoice->frequency,
			'due_date' => $this->invoice->due_date,
			'first_pay_date' => optional($this->invoice->recuring_invoice)->first_arrival_date ?? '',
			'items' => $this->invoice->items,
			'subtotal' => $this->invoice->subtotal,
			'tax' => $this->invoice->tax,
			'taxRate' => $this->invoice->tax_rate,
			'vat' => $this->invoice->vat,
			'vatRate' => $this->invoice->vat_rate,
			'grandTotal' => $this->invoice->grand_total,
			'note' => $this->invoice->note ?? null,
		]);
		return $pdf->stream();
	}
}
