<?php

namespace App\Http\Controllers;

use App\Http\Requests\InvoiceStoreRequest;
use App\Mail\ReminderMail;
use App\Mail\SendInvoiceMail;
use App\Models\ChargesLimit;
use App\Models\Currency;
use App\Models\Gateway;
use App\Models\Invoice;
use App\Models\RecuringInvoice;
use App\Models\Wallet;
use App\Traits\PaymentTrait;
use Carbon\Carbon;
use Dompdf\Dompdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Stevebauman\Purify\Facades\Purify;

class InvoiceController extends Controller
{
    use PaymentTrait;

    public function index(Request $request)
    {
        $userId = Auth::id();
        $currencies = Currency::select('id', 'code', 'name')->orderBy('code', 'ASC')->get();

        $invoices = Invoice::query()
            ->with(['sender', 'currency'])
            ->where('sender_id', $userId)
            ->filter($request->all())
            ->latest()
            ->paginate(20)
            ->appends($request->query());

        return view('user.invoice.index', compact('currencies', 'invoices'));
    }

	public function create()
	{
		$data['basicControl'] = basicControl();
		$data['template'] = null;
		$data['currencies'] = Currency::select('id', 'code', 'name', 'symbol')->orderBy('code', 'ASC')->get();
		return view('user.invoice.create', $data);
	}

	public function store(InvoiceStoreRequest $request)
	{
		DB::beginTransaction();
		if ($request->payment == 2 || $request->payment == 3) {
			$recuringInvoice = new RecuringInvoice();

			if ($request->payment == 2) {
				$recuringInvoice->number_of_payments = $request->num_payment;
				$recuringInvoice->first_arrival_date = Carbon::createFromFormat('d/m/Y', $request->first_pay_date);
				$recuringInvoice->last_arrival_date = Carbon::createFromFormat('d/m/Y', $request->first_pay_date)->addWeeks(($request->num_payment - 1));

			} elseif ($request->payment == 3) {

				$recuringInvoice->number_of_payments = $request->num_payment;
				$recuringInvoice->first_arrival_date = Carbon::createFromFormat('d/m/Y', $request->first_pay_date);
				$recuringInvoice->last_arrival_date = Carbon::createFromFormat('d/m/Y', $request->first_pay_date)->addMonths(($request->num_payment - 1));
			}

			$recuringInvoice->subtotal = @$request->subtotal;
			$recuringInvoice->tax = $request->tax;
			$recuringInvoice->vat = $request->vat;
			$recuringInvoice->tax_rate = $request->taxRate;
			$recuringInvoice->vat_rate = $request->vatRate;
			$recuringInvoice->grand_total = $request->garndtotal;

			$recuringInvoice->save();
		}

		$invoice = new Invoice();
		$invoice->sender_id = auth()->id();
		if ($request->payment == 2 || $request->payment == 3) {
			$invoice->recuring_invoice_id = $recuringInvoice->id;
		}
		$invoice->customer_email = $request->customer_email;
		$invoice->invoice_number = $request->invoice_number;
		$invoice->subtotal = $request->subtotal;
		$invoice->tax = $request->tax;
		$invoice->vat = $request->vat;
		$invoice->tax_rate = $request->taxRate;
		$invoice->vat_rate = $request->vatRate;
		$invoice->grand_total = $request->garndtotal;
		$invoice->frequency = $request->payment;

		if (isset($request->due_date)) {
			$invoice->due_date = Carbon::createFromFormat('d/m/Y', $request->due_date);
		} else {
			$invoice->due_date = $recuringInvoice->first_arrival_date;
		}
		$basicControl = basicControl();
		$invoice->charge_pay = $basicControl->invoice_charge;
		$invoice->currency_id = $request->currency;
		$charge = $this->checkInitiateAmountValidate($request->currency, $request->garndtotal);

		if ($charge == 'false') {
			return 0;
		}
		$invoice->percentage = $charge['percentage_charge'];
		$invoice->charge_percentage = $charge['valueAfterPercent'];
		$invoice->charge_fixed = $charge['fixed_charge'];
		$invoice->charge = $charge['charge'];

		$invoice->save();

		if ($request->button_name == 'send') {
			$data = $invoice->id . '|' . $request->customer_email;
			$invoice = Invoice::findOrFail($invoice->id);
			$invoice->has_slug = $this->encrypt($data);
			$invoice->note = $request->note;
			$invoice->save();
		}
		if (count($request->items) > 0) {
			foreach ($request->items as $key => $item) {
				$invoice->items()->create([
					'title' => $item['title'] ?? 'N/A',
					'price' => $item['price'] ?? 0.00,
					'description' => $item['description'] ?? null,
					'quantity' => $item['quantity'] ?? 0.00,
					'subtotal' => $item['quantity'] * $item['price'],
				]);
			}
		}
		DB::commit();
		session()->flash('success', 'Invoice send successfully');
		if ($request->button_name == 'send') {
			if ($request->payment != 2 && $request->payment != 3) {
				Mail::to($request->customer_email)->queue(new SendInvoiceMail((object)$invoice));
			}
		}
		return response()->json([
			'status' => 'success',
			'url' => route('user.invoice.create'),
		]);
	}

	public function generatePdf(Request $request)
	{
		$res = json_decode($request->invoice);
		$data = (array)$res;
		$data['customer_email'] = @$data['customer']->email_address;

		if ($data['due_date']) {
			$data['due_date'] = Carbon::createFromFormat('d/m/Y', $data['due_date']);
		}

		if ($data['first_pay_date']) {
			$data['first_pay_date'] = Carbon::createFromFormat('d/m/Y', $data['first_pay_date']);
		}

        $user = auth()->user();
		$data['basic'] = basicControl();
		$data['email'] = $user->email;
		$data['phone'] = $user->phone_code . $user->phone;

        $dompdf = new Dompdf();
        $dompdf->loadHtml(view('user.invoice.invoicePdf.pdf', $data)->render());
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        if ($data['clickBtn'] == 0) {
            return response($dompdf->output())->header('Content-Type', 'application/pdf');
        } else {
            return response()->streamDownload(function () use ($dompdf) {
                echo $dompdf->output();
            }, 'invoice.pdf');
        }
	}

	public function encrypt($data)
	{
		return implode(unpack("H*", $data));
	}

	public function currencyCheck(Request $request)
	{
		$data = $this->checkInitiateAmountValidate($request->id, null);
		return response()->json([
			'status' => 'success',
			'value' => $data
		]);
	}

	public function checkInitiateAmountValidate($currency_id, $gradTotal = null)
	{
		$chargesLimit = ChargesLimit::with('currency')
            ->where(['currency_id' => $currency_id, 'transaction_type_id' => config('transactionType.invoice'), 'is_active' => 1])
            ->first();
		$wallet = Wallet::firstOrCreate(['user_id' => Auth::id(), 'currency_id' => $currency_id]);


		$min_limit = 0;
		$max_limit = 0;
		$fixed_charge = 0;
		$percentage = 0;

		if ($chargesLimit) {
			$percentage = getAmount($chargesLimit->percentage_charge, 8);
			$valueAfterPercent = getAmount(($gradTotal * $percentage) / 100, 8);
			$fixed_charge = getAmount($chargesLimit->fixed_charge, 8);
			$min_limit = getAmount($chargesLimit->min_limit, 8);
			$max_limit = getAmount($chargesLimit->max_limit, 8);
			$charge = getAmount($valueAfterPercent + $fixed_charge, 8);
		}
		if ($gradTotal != null) {
			if (($gradTotal + $charge) > $max_limit || ($gradTotal + $charge) < $min_limit) {
				return "false";
			}
		}

		$data['fixed_charge'] = $fixed_charge;
		$data['percentage_charge'] = $percentage;
		$data['min_limit'] = $min_limit;
		$data['max_limit'] = $max_limit;
		$data['valueAfterPercent'] = $valueAfterPercent;
		$data['charge'] = $charge;

		return $data;
	}

	public function showPublicInvoice($hash_slug)
	{
		$data['invoice'] = Invoice::where('has_slug', $hash_slug)->firstOrFail();
		return view('user.invoice.publicPayment.payment-show', $data);
	}

	public function publicInvoicePaymentConfirm(Request $request, $hash_slug)
	{
		$reqStatus = $request->status;
		$invoice = Invoice::where('has_slug', $hash_slug)->firstOrFail();

		if ($invoice->status == null && ($reqStatus == 2 || $reqStatus == 5)) {
			if ($reqStatus == 5) {
				$invoice->status = 'rejected';
				$invoice->rejected_at = Carbon::now();
				$invoice->save();
				return redirect('/')->with('success', 'Transaction canceled');
			} else {
				return redirect(route('invoice.public.payment', $hash_slug));
			}
		}
	}

	public function rejectInvoiceFromEmail($hash_slug)
	{
		$invoice = Invoice::where('has_slug', $hash_slug)->where('status', null)->firstOrFail();
		$invoice->status = 'rejected';
		$invoice->rejected_at = Carbon::now();
		$invoice->save();
		return redirect(route('invoice.public.payment', $hash_slug));
	}

	public function invoicePublicPayment(Request $request, $hash_slug)
	{
		$invoice = Invoice::doesntHave('successDepositable')->where('status', null)->where('has_slug', $hash_slug)->firstOrFail();

		if ($invoice->charge_pay == 1) {
			$invoiceCharge = $invoice->charge;
		} else {
			$invoiceCharge = 0;
		}

		if ($request->isMethod('get')) {
			$methods = Gateway::getActiveMethods();
			return view('user.invoice.publicPayment.payment', compact('methods', 'invoice', 'invoiceCharge'));
		}
        elseif ($request->isMethod('post')) {
			$purifiedData = Purify::clean($request->all());
			$validationRules = [
				'amount' => 'required|numeric|min:1|not_in:0',
				'currency' => 'required|integer|min:1|not_in:0',
				'methodId' => 'required|integer|min:1|not_in:0',
			];

			$validate = Validator::make($purifiedData, $validationRules);
			if ($validate->fails()) {
				return back()->withErrors($validate)->withInput();
			}

			$purifiedData = (object)$purifiedData;
			$amount = $purifiedData->amount;
			$currency_id = $purifiedData->currency;
			$methodId = $purifiedData->methodId;

            $checkAmountValidate = $this->validatePayment($amount,$currency_id,config('transactionType.invoice'),$methodId,$invoiceCharge);
            if (!$checkAmountValidate['status']) {
				return back()->withInput()->with('error', $checkAmountValidate['message']);
			}

            $deposit  = $this->createDeposit($checkAmountValidate, Invoice::class, $invoice->id);
			return redirect(route('payment.process', $deposit->trx_id));
		}
	}


	public function viewInvoice($id)
	{
		$data['invoice'] = Invoice::findOrFail($id);
		return view('user.invoice.details', $data);
	}

	public function downloadPDF($Id)
	{
		$invoice = Invoice::with(['items', 'recuring_invoice'])->findOrFail($Id);

		$data = [
			'invoice_number' => $invoice->invoice_number,
			'customer_email' => $invoice->customer_email ?? $invoice->customer?->email_address,
			'email' => optional($invoice->sender)->email,
			'phone' => optional($invoice->sender)->mobile,
			'currency' => optional($invoice->currency)->symbol,
			'payment' => $invoice->frequency,
			'due_date' => $invoice->due_date,
			'first_pay_date' => optional($invoice->recuring_invoice)->first_arrival_date ?? '',
			'items' => $invoice->items,
			'subtotal' => $invoice->subtotal,
			'tax' => $invoice->tax,
			'taxRate' => $invoice->tax_rate,
			'vat' => $invoice->vat,
			'vatRate' => $invoice->vat_rate,
			'grandTotal' => $invoice->grand_total,
			'note' => $invoice->note,
		];

        $dompdf = new Dompdf();
        $dompdf->loadHtml(view('user.invoice.invoicePdf.pdf', $data)->render());
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        return response()->streamDownload(function () use ($dompdf) {
            echo $dompdf->output();
        }, 'invoice.pdf');

	}

	public function invoiceReminder(Request $request)
	{
		$invoice = Invoice::where('id', $request->invoiceId)->firstOrFail();
		if ($invoice->status == 'paid' || $invoice->status == 'rejected') {
			return back()->with('error', 'you can not send reminder complete invoice');
		}

		Mail::to($invoice->customer_email)->queue(new ReminderMail((object)$invoice));
		$invoice->reminder_at = Carbon::now();
		$invoice->save();
		return back()->with('success', 'Reminder send');
	}

}
