<?php

namespace App\Http\Controllers;

use App\Models\ApiOrder;
use App\Models\ApiOrderTest;
use App\Models\Deposit;
use App\Models\Gateway;
use App\Models\ProductOrder;
use App\Models\Store;
use App\Traits\Notify;
use App\Traits\Upload;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Facades\App\Services\BasicService;
use Exception;
use Illuminate\Support\Facades\Validator;


class PaymentController extends Controller
{

    use Upload, Notify;

    public function __construct()
    {
        $this->theme = template();
    }

    public function depositConfirm($trx_id)
    {
        try {
            $deposit = Deposit::with('user', 'depositable')->where(['trx_id' => $trx_id, 'status' => 0])->first();
            if (!$deposit) {
                throw new Exception('Invalid Payment Request.');
            }
            $gateway = Gateway::findOrFail($deposit->payment_method_id);
            if (!$gateway) {
                throw new Exception('Invalid Payment Gateway.');
            }

            if (999 < $gateway->id) {
                return view('user.payment.manual', compact('deposit'));
            }

            $gatewayObj = 'App\\Services\\Gateway\\' . $gateway->code . '\\Payment';
            $data = $gatewayObj::prepareData($deposit, $gateway);
            $data = json_decode($data);

        } catch (Exception $exception) {
            session()->flash('warning', 'Something went wrong. Please try again.');
            return back()->with('error', $exception->getMessage());
        }

        if (isset($data->error)) {
            return back()->with('error', $data->message);
        }

        if (isset($data->redirect)) {
            return redirect($data->redirect_url);
        }

        $page_title = 'Payment Confirm';
        return view( $data->view, compact('data', 'page_title', 'deposit'));

    }

    public function gatewayIpn(Request $request, $code, $trx = null, $type = null)
    {
        if (isset($request->m_orderid)) {
            $trx = $request->m_orderid;
        }

        if ($code == 'coinbasecommerce') {
            $gateway = Gateway::where('code', $code)->first();
            $postdata = file_get_contents("php://input");
            $res = json_decode($postdata);

            if (isset($res->event)) {
                $deposit = Deposit::with('user')->where('trx_id', $res->event->data->metadata->trx)->orderBy('id', 'DESC')->first();
                $sentSign = $request->header('X-Cc-Webhook-Signature');
                $sig = hash_hmac('sha256', $postdata, $gateway->parameters->secret);

                if ($sentSign == $sig) {
                    if ($res->event->type == 'charge:confirmed' && $deposit->status == 0) {
                        BasicService::preparePaymentUpgradation($deposit);
                    }
                }
            }
            session()->flash('success', 'You request has been processing.');

            if ($deposit->depositable_type == 'App\Models\ProductOrder') {
                $order = ProductOrder::with(['store'])->where('status', 1)->where('utr', $deposit->trx_id)->firstOrFail();
                $link = optional($order->store)->link ?? '@';
                return redirect()->route('order.success', [$link, $order->order_number]);
            }
            if ($deposit->depositable_type == ApiOrder::class || $deposit->depositable_type == ApiOrderTest::class) {
                $order = $deposit->depositable;
                $data = [
                    'status' => 'success',
                    'id' => $order->utr,
                    'currency' => optional($order->currency)->code,
                    'amount' => $order->amount,
                    'order_id' => $order->order_id,
                ];
                $data = http_build_query($data);
                $url = $order->redirect_url;
                $url = $url . '?' . $data;
                return redirect()->away($url);
            }

            return redirect()->route('success');
        }

        try {
            $gateway = Gateway::where('code', $code)->first();
            if (!$gateway) {
                throw new Exception('Invalid Payment Gateway.');
            }
            if (isset($trx)) {
                $deposit = Deposit::with('user')->where('trx_id', $trx)->first();
                if (!$deposit) throw new Exception('Invalid Payment Request.');
            }

            $gatewayObj = 'App\\Services\\Gateway\\' . $code . '\\Payment';
            $data = $gatewayObj::ipn($request, $gateway, $deposit ?? null, $trx ?? null, $type ?? null);

        } catch (\Exception $exception) {
            return back()->with('error', $exception->getMessage());
        }
        if (isset($data['redirect'])) {
            return redirect($data['redirect'])->with($data['status'], $data['msg']);
        }
    }


    public function fromSubmit(Request $request, $trx_id)
    {
        if ($request->isMethod('get')) {
            return redirect()->route('payment.process', $trx_id);
        }

        $data = Deposit::where('trx_id', $trx_id)->orderBy('id', 'DESC')->with(['gateway', 'user'])->first();
        if (is_null($data)) {
            return redirect('/')->with('error', 'Invalid Request');
        }
        if ($data->status != 0) {
            return redirect('/')->with('error', 'Invalid Request');
        }

        $params = optional($data->gateway)->parameters;
        $reqData = $request->except('_token', '_method');
        $rules = [];
        if ($params !== null) {
            foreach ($params as $key => $cus) {
                $rules[$key] = [$cus->validation == 'required' ? $cus->validation : 'nullable'];
                if ($cus->type === 'file') {
                    $rules[$key][] = 'image';
                    $rules[$key][] = 'mimes:jpeg,jpg,png';
                    $rules[$key][] = 'max:2048';
                } elseif ($cus->type === 'text') {
                    $rules[$key][] = 'max:191';
                } elseif ($cus->type === 'number') {
                    $rules[$key][] = 'integer';
                } elseif ($cus->type === 'textarea') {
                    $rules[$key][] = 'min:3';
                    $rules[$key][] = 'max:300';
                }
            }
        }

        $validator = Validator::make($reqData, $rules);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $reqField = [];
        if ($params != null) {
            $submittedFields = array_merge(
                $request->except('_token', '_method', 'type'),
                $request->allFiles()
            );

            foreach ($submittedFields as $k => $v) {
                foreach ($params as $inKey => $inVal) {
                    if ($k == $inKey) {
                        if ($inVal->type == 'file' && $request->hasFile($inKey)) {
                            try {
                                $file = $this->fileUpload($request[$inKey], config('filelocation.deposit.path'), null, null, 'webp', 80, null, null);
                                $reqField[$inKey] = [
                                    'field_name' => $inVal->field_name,
                                    'field_value' => $file['path'],
                                    'field_driver' => $file['driver'],
                                    'validation' => $inVal->validation,
                                    'type' => $inVal->type,
                                ];
                            } catch (\Exception $exp) {
                                session()->flash('error', 'Could not upload your ' . $inKey);
                                return back()->withInput();
                            }
                        } else {
                            $reqField[$inKey] = [
                                'field_name' => $inVal->field_name,
                                'validation' => $inVal->validation,
                                'field_value' => $v,
                                'type' => $inVal->type,
                            ];
                        }
                    }
                }
            }
        }

        $data->update([
            'information' => $reqField,
            'created_at' => Carbon::now(),
            'status' => 2,
        ]);

        $msg = [
            'username' => optional($data->user)->username,
            'amount' => currencyPosition($data->amount),
            'gateway' => optional($data->gateway)->name
        ];
        $action = [
            "name" => optional($data->user)->name,
            "image" => getFile(optional($data->user)->image_driver, optional($data->user)->image),
            "link" => '#',
            "icon" => "fa fa-money-bill-alt text-white"
        ];

        $this->adminPushNotification('PAYMENT_REQUEST', $msg, $action);
        $this->adminFirebasePushNotification('PAYMENT_REQUEST', $msg, $action);
        $this->adminMail('PAYMENT_REQUEST', $msg);

        session()->flash('success', 'You request has been taken.');

        if ($data->depositable_type == 'App\Models\ProductOrder') {
            
            $order = ProductOrder::with(['store'])->where('status', 0)->where('utr', $data->trx_id)->firstOrFail();
            $link = optional($order->store)->link ?? '@';
            return redirect()->route('order.success', [$link, $order->order_number]);
        }
        if ($data->depositable_type == ApiOrder::class || $data->depositable_type == ApiOrderTest::class) {
            $order = $data->depositable;
            $data = [
                'status' => 'success',
                'id' => $order->utr,
                'currency' => optional($order->currency)->code,
                'amount' => $order->amount,
                'order_id' => $order->order_id,
            ];
            $data = http_build_query($data);
            $url = $order->redirect_url;
            $url = $url . '?' . $data;
            return redirect()->away($url);
        }
        
        
        return to_route('success');

    }

    public function success()
    {
        return view('success');
    }

    public function failed()
    {
        return view('failed');
    }


    public function orderSuccess($link, $orderNumber)
    {
        $store = Store::with('user.storeCurrency')->where('link', $link)->firstOrFail();
        $data['order'] = ProductOrder::with(['orderDetails', 'orderDetails.product'])->where('order_number', $orderNumber)->whereIn('status', [0,1])->firstOrFail();
        return view('user.store.orderConfirm', $data, compact('link', 'store'));
    }


}
