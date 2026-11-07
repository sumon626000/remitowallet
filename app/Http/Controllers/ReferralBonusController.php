<?php

namespace App\Http\Controllers;

use App\Models\ReferralBonus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Stevebauman\Purify\Facades\Purify;

class ReferralBonusController extends Controller
{
	public function show(Request $request)
	{
		if ($request->isMethod('get')) {
			$referralBonuses = ReferralBonus::all();
			return view('admin.referralBonus.show', compact('referralBonuses'));
		}
        elseif ($request->isMethod('post')) {
			$purifiedData = Purify::clean($request->all());
			$validator = Validator::make($purifiedData, [
				'referral_on' => 'required',
				'level.*' => 'required|integer|min:1',
				'amount.*' => 'required|numeric',
			], [
				'min' => 'This field must be at least :min characters.',
				'string' => 'This field must be :string.',
				'required' => 'This field is required.',
			]);
			if ($validator->fails()) {
				return back()->withErrors($validator)->withInput();
			}

			ReferralBonus::where('referral_on', $request->referral_on)->delete();
			for ($a = 0; $a < count($request->level); $a++) {
				ReferralBonus::create([
					'level' => $request->level[$a],
					'amount' => $request->amount[$a],
					'status' => $request->status[$a],
					'calcType' => $request->calcType[$a],
					'minAmount' => $request->minAmount[$a],
					'referral_on' => $request->referral_on,
				]);
			}
			return back()->with('success', 'Create Successfully');
		}
	}
}
