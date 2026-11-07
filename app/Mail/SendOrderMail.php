<?php

namespace App\Mail;

use App\Models\Currency;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendOrderMail extends Mailable
{
	use Queueable, SerializesModels;

	/**
	 * Create a new message instance.
	 *
	 * @return void
	 */
	public $order;
	public $subtotal;
	public $currencySymbol;

	public function __construct($order)
	{
		$subtotal = 0;
		$this->order = $order;
		$this->currencySymbol = Currency::select('symbol')->findOrFail(optional($order->store->user)->store_currency_id);

		foreach ($order->orderDetails as $orderDetail) {
			$subtotal += $orderDetail->total_price;
		}
		$this->subtotal = $subtotal;
	}

	/**
	 * Build the message.
	 *
	 * @return $this
	 */
	public function build()
	{
        $basic = basicControl();
		$mailMessage = $this->subject("Payment has been completed")
			->from($basic->sender_email,$basic->sender_email_name)
			->attachData($this->pdf(), $this->order->order_number . '_Order.pdf', [
				'mime' => 'application/pdf',
			]);

		$mailMessage->view('user.store.orderPdf.order', [
			'orderNumber' => $this->order->order_number,
			'date' => dateTime($this->order->created_at),
			'buyerName' => $this->order->fullname,
			'buyerEmail' => $this->order->email ?? null,
			'buyerPhone' => $this->order->phone ?? null,
			'buyerAddress' => $this->order->detailed_address ?? null,
			'shopName' => optional($this->order->store)->name ?? null,
			'shopImage' => getFile(config('location.store.path') . optional($this->order->store)->image),
			'items' => $this->order->orderDetails,
			'currency' => $this->currencySymbol->symbol,
			'subtotal' => $this->subtotal,
			'shipping' => $this->order->shipping_charge ?? 0,
			'totalAmount' => $this->subtotal + $this->order->shipping_charge,
		]);

	}

	private function pdf()
	{

		$pdf = PDF::loadView('user.store.orderPdf.order', [
			'orderNumber' => $this->order->order_number,
			'date' => dateTime($this->order->created_at),
			'buyerName' => $this->order->fullname,
			'buyerEmail' => $this->order->email ?? null,
			'buyerPhone' => $this->order->phone ?? null,
			'buyerAddress' => $this->order->detailed_address ?? null,
			'shopName' => optional($this->order->store)->name ?? null,
			'shopImage' => getFile(config('location.store.path') . optional($this->order->store)->image),
			'items' => $this->order->orderDetails,
			'currency' => $this->currencySymbol->symbol,
			'subtotal' => $this->subtotal,
			'shipping' => $this->order->shipping_charge ?? 0,
			'totalAmount' => $this->subtotal + $this->order->shipping_charge,
		]);

		return $pdf->stream();
	}
}
