<?php

namespace App\Http\Controllers;

use App\Mail\SendMail;
use App\Models\NotificationTemplate;
use App\Models\PageDetail;
use App\Models\Subscribe;
use App\Traits\Frontend;
use hisorange\BrowserDetect\Exceptions\Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Stevebauman\Purify\Facades\Purify;
use Facades\App\Http\Controllers\PaymentController;
use Facades\App\Console\Commands\PayoutCurrencyUpdateCron;

class FrontendController extends Controller
{
    use Frontend;

    public function page($slug = '/')
    {
        try {

            $selectedTheme = getTheme();

            $existingSlugs = collect([]);
            DB::table('pages')->select('slug')->get()->map(function ($item) use ($existingSlugs) {
                $existingSlugs->push($item->slug);
            });
            if (!in_array($slug, $existingSlugs->toArray())) {
                throw new Exception("Page Not Found!", 404);
            }

            $pageDetails = PageDetail::with('page')
                ->whereHas('page', function ($query) use ($slug, $selectedTheme) {
                    $query->where(['slug' => $slug, 'template_name' => $selectedTheme]);
                })
                ->first();

            $pageSeo = [
                'page_title' => optional(optional($pageDetails)->page)->page_title ?? '',
                'meta_title' => optional(optional($pageDetails)->page)->meta_title,
                'meta_keywords' => implode(',', optional(optional($pageDetails)->page)->meta_keywords ?? []),
                'meta_description' => optional(optional($pageDetails)->page)->meta_description,
                'og_description' => optional(optional($pageDetails)->page)->og_description,
                'meta_robots' => optional(optional($pageDetails)->page)->meta_robots,
                'meta_image' => $pageDetails?->page
                    ? getFile($pageDetails->page->meta_image_driver, $pageDetails->page->meta_image)
                    : null,
                'breadcrumb_status' => optional(optional($pageDetails)->page)->breadcrumb_status,
                'breadcrumb_image' => $pageDetails?->page?->breadcrumb_image
                    ? getFile($pageDetails?->page?->breadcrumb_image_driver, $pageDetails->page->breadcrumb_image)
                    : null,
            ];

            $sectionsData = $this->getSectionsData($pageDetails?->sections, $pageDetails?->content, $selectedTheme);

            return view("themes.{$selectedTheme}.page", compact('sectionsData', 'pageSeo'));

        } catch (\Exception $exception) {
            $this->handleDatabaseException($exception);
        }

    }


    public function contactSend(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:50',
            'email' => 'required|email|max:91',
            'subject' => 'required|max:100',
            'message' => 'required|max:1000',
        ]);
        $requestData = $request->except('_token', '_method');

        $name = $requestData['name'];
        $email_from = $requestData['email'];
        $subject = $requestData['subject'];
        $message = $requestData['message'] . "<br>Regards<br>" . $name;
        $from = $email_from;

        Mail::to(basicControl()->sender_email)->send(new SendMail($from, $subject, $message));
        return back()->with('success', 'Your message has been sent');
    }

    public function subscribe(Request $request)
    {
        $purifiedData = Purify::clean($request->all());
        $validationRules = [
            'email' => 'required|email|min:8|max:100|unique:subscribes',
        ];
        $validate = Validator::make($purifiedData, $validationRules);
        if ($validate->fails()) {
            session()->flash('error', 'Email Field is required');
            return back()->withErrors($validate)->withInput();
        }
        $purifiedData = (object)$purifiedData;

        $subscribe = new Subscribe();
        $subscribe->email = $purifiedData->email;
        $subscribe->save();

        return back()->with('success', 'Subscribed successfully');
    }


}
