<?php

namespace App\Http\Controllers\Admin;

use App\Models\SecurityQuestion;
use Illuminate\Http\Request;
use Stevebauman\Purify\Facades\Purify;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;

class SecurityQuestionController extends Controller
{
	public function index()
	{
		$data['securityQuestions'] = SecurityQuestion::all();
		return view('admin.securityQuestion.index', $data);
	}

	public function create()
	{
		return view('admin.securityQuestion.create');
	}

	public function store(Request $request)
	{
		$purifiedData = Purify::clean($request->all());
		$validationRules = [
			'question' => 'required|min:10|max:250',
		];

		$validate = Validator::make($purifiedData, $validationRules);
		if ($validate->fails()) {
			return back()->withErrors($validate)->withInput();
		}

		$purifiedData = (object)$purifiedData;
		$securityQuestion = new SecurityQuestion();
		$securityQuestion->question = $purifiedData->question;
		$securityQuestion->save();

		return redirect(route('admin.securityQuestion.index'))->with('success', 'Security Question Successfully Saved');
	}

	public function edit(SecurityQuestion $securityQuestion)
	{
		return view('admin.securityQuestion.edit')
			->with('securityQuestion', $securityQuestion);
	}

	public function update(Request $request, SecurityQuestion $securityQuestion)
	{
		$purifiedData = Purify::clean($request->all());
		$validationRules = [
			'question' => 'required|min:10|max:250',
		];

		$validate = Validator::make($purifiedData, $validationRules);
		if ($validate->fails()) {
			return back()->withErrors($validate)->withInput();
		}

		$purifiedData = (object)$purifiedData;
		$securityQuestion->question = $purifiedData->question;
		$securityQuestion->save();

		return redirect(route('admin.securityQuestion.index'))->with('success', 'Security Question Successfully Updated');
	}
}
