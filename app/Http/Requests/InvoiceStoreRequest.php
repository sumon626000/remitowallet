<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InvoiceStoreRequest extends FormRequest
{
	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize()
	{
		return true;
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules()
	{
		return [
			'invoice_number' => ['required'],
			'customer_email' => ['required'],
			'due_date' => ['required_if:payment,1'],
			'first_pay_date' => ['required_if:payment,2,3'],
			'num_payment' => ['required_if:payment,2,3'],
		];
	}
}
