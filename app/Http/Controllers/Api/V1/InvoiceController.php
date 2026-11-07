<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Mail\ReminderMail;
use App\Mail\SendInvoiceMail;
use App\Models\ChargesLimit;
use App\Models\Currency;
use App\Models\Invoice;
use App\Models\RecuringInvoice;
use App\Models\Wallet;
use App\Traits\ApiValidation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Stevebauman\Purify\Facades\Purify;

class InvoiceController extends Controller
{
	use ApiValidation;

	public function invoiceCreate()
	{
		try {
			$data['image'] = getFile(config('filelocation.logo.path') . 'logo.png');
			$data['currencies'] = Currency::select('id', 'code', 'name', 'symbol')->orderBy('code', 'ASC')->get();
			return response()->json($this->withSuccess($data));
		} catch (\Exception $e) {
			return response()->json($this->withErrors($e->getMessage()));
		}
	}

	public function invoiceStore(Request $request)
	{

		$purifiedData = Purify::clean($request->all());
		$validationRules = [
			'invoice_number' => ['required'],
			'customer_email' => ['required'],
			'due_date' => ['required_if:payment,1'],
			'first_pay_date' => ['required_if:payment,2,3'],
			'num_payment' => ['required_if:payment,2,3'],
		];
		$validate = Validator::make($purifiedData, $validationRules);

		if ($validate->fails()) {
			return response()->json($this->withErrors(collect($validate->errors())->collapse()[0]));
		}

		DB::beginTransaction();

		try {
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

			if (!$charge['status']) {
				return response()->json($this->withErrors($charge['message']));
			}
			$invoice->percentage = $charge['percentage_charge'];
			$invoice->charge_percentage = $charge['valueAfterPercent'];
			$invoice->charge_fixed = $charge['fixed_charge'];
			$invoice->charge = $charge['charge'];

			$invoice->save();

			if ($request->button_name == 'send') {
				$data = $invoice->id . '|' . $request->customer_email;
				$invoice = Invoice::find($invoice->id);
				$invoice->has_slug = $this->encrypt($data);
				$invoice->note = $request->note;
				$invoice->save();
			}

			if (count($request->items) > 1) {
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

			if ($request->button_name == 'send') {
				if ($request->payment != 2 && $request->payment != 3) {

					Mail::to($request->customer_email)
						->queue(new SendInvoiceMail((object)$invoice));
				}
			}

			return response()->json($this->withSuccess('Invoice send successfully'));
		} catch (\Exception $e) {
			DB::rollBack();
			return response()->json($this->withErrors($e->getMessage()));
		}

	}

	public function checkInitiateAmountValidate($currency_id, $gradTotal = null)
	{
		$chargesLimit = ChargesLimit::with('currency')->where(['currency_id' => $currency_id, 'transaction_type_id' => config('transactionType.invoice'), 'is_active' => 1])->first();
		$wallet = Wallet::firstOrCreate(['user_id' => Auth::id(), 'currency_id' => $currency_id]);
		if (!$chargesLimit) {
			return [
				'status' => false,
				'message' => "Please add charge and limit"
			];
		}

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
				return [
					'status' => false,
					'message' => "Min Limit $min_limit and Maximum Limit $max_limit"
				];
			}
		}

		$data['status'] = true;
		$data['fixed_charge'] = $fixed_charge;
		$data['percentage_charge'] = $percentage;
		$data['min_limit'] = $min_limit;
		$data['max_limit'] = $max_limit;
		$data['valueAfterPercent'] = $valueAfterPercent;
		$data['charge'] = $charge;

		return $data;
	}

	public function encrypt($data)
	{
		return implode(unpack("H*", $data));
	}

    public function invoiceList(Request $request)
    {
        try {
            $userId = Auth::id();

            $data['invoices'] = Invoice::query()
                ->with(['sender', 'currency'])
                ->where('sender_id', $userId)
                ->filter($request->all())
                ->latest()
                ->paginate(20)
                ->through(fn($invoice) => $invoice->transformData());

            $data['currencies'] = Currency::select('id', 'code', 'name')->orderBy('code', 'ASC')->get();

            return response()->json($this->withSuccess($data));
        } catch (\Exception $e) {
            return response()->json($this->withErrors($e->getMessage()));
        }
    }


	public function invoiceView($id)
	{
		try {
            $data['currencies'] = Currency::select('id', 'code', 'name', 'symbol')->orderBy('code', 'ASC')->get();
			$data['invoice'] = Invoice::with(['items'])->find($id);
            if ($data['invoice'] && is_null($data['invoice']->status)) {
                $data['invoice']->status = 'unpaid';
            }
			if (!$data['invoice']) {
				return response()->json($this->withErrors('Record not found'));
			}
			$data['payToPhone'] = auth()->user()->mobile;
			$data['payToEmail'] = auth()->user()->email;
			$data['image'] = basicControl()?->footerLogo();
			$data['requestLink'] = route('public.invoice.show', $data['invoice']->has_slug);
			return response()->json($this->withSuccess($data));
		} catch (\Exception $e) {
			return response()->json($this->withErrors($e->getMessage()));
		}
	}

	public function invoiceReminder(Request $request)
	{
		try {
			$invoice = Invoice::where('id', $request->invoiceId)->first();
			if (!$invoice) {
				return response()->json($this->withErrors('Record not found'));
			}
			if ($invoice->status == 'paid' || $invoice->status == 'rejected') {
				return response()->json($this->withErrors('you can not send reminder complete invoice'));
			}

			Mail::to($invoice->customer_email)
				->queue(new ReminderMail((object)$invoice));

			$invoice->reminder_at = Carbon::now();
			$invoice->save();
			return response()->json($this->withSuccess('Reminder send'));
		} catch (\Exception $e) {
			return response()->json($this->withErrors($e->getMessage()));
		}
	}
}
