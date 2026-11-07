<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Models\Gateway;
use App\Models\QRCode;
use App\Models\User;
use App\Traits\PaymentTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Stevebauman\Purify\Facades\Purify;

class QrCodePaymentController extends Controller
{
    use PaymentTrait;

	public function qrPayment(Request $request, $link)
	{
		if (basicControl()->qr_payment == 0) {
            return redirect('/')->with('error', __('QR payment is currently disabled.'));
        }
		$user = User::with(['qrCurrency'])->where('qr_link', $link)->firstOrFail();
		if ((empty($user->qrCurrency))) {
            return redirect('/');
        }

		if ($request->isMethod('get')) {
			$methods = Gateway::getActiveMethods();
			return view('user.qrCode.payment', compact('methods', 'user'));
		}
        elseif ($request->isMethod('post')) {
			$purifiedData = Purify::clean($request->all());

			$validationRules = [
				'amount' => 'required|numeric|min:1|not_in:0',
				'methodId' => 'required|integer|min:1|not_in:0',
				'email' => 'required'
			];

			$validate = Validator::make($purifiedData, $validationRules);
			if ($validate->fails()) {
				return back()->withErrors($validate)->withInput();
			}

			$purifiedData = (object)$purifiedData;
			$amount = $purifiedData->amount;
			$currency_id = $user->qr_currency_id;
			$methodId = $purifiedData->methodId;
            $currency = Currency::findOrFail($currency_id); //check currency

            $checkAmountValidate = $this->validatePayment($amount,$currency_id,config('transactionType.deposit'),$methodId);
			if (!$checkAmountValidate['status']) {
				return back()->withInput()->with('error', $checkAmountValidate['message']);
			}

			$qrCode = new QRCode();
			$qrCode->user_id = $user->id;
			$qrCode->gateway_id = $methodId;
			$qrCode->currency_id = $currency_id;
			$qrCode->email = $purifiedData->email;
			$qrCode->charge = $checkAmountValidate['charge'];
			$qrCode->amount = $amount;
			$qrCode->status = 0;
			$qrCode->save();

            $deposit  = $this->createDeposit($checkAmountValidate, QRCode::class, $qrCode->id);

			return redirect(route('payment.process', $deposit->trx_id));
		}
	}

}
