<?php

namespace Modules\Agent\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\Language;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Modules\Agent\Models\AgentSetting;
use Yajra\DataTables\Facades\DataTables;

class AdminController extends Controller
{
    public function settings()
    {
        $settings = AgentSetting::query()->get();
        return view('agent::admin.settings.index', compact('settings'));
    }

    public function settingsCreate(Request $request)
    {
        $currencies = Currency::query()->where('is_active',1)->get();
        return view('agent::admin.settings.create', compact('currencies'));
    }

    public function settingsEdit($id)
    {
        $agent = AgentSetting::findOrFail($id);
        $title = __('Edit :type Settings for :currency Currency', [
            'type' => Str::headline($agent->type),
            'currency' => $agent->currency->code ?? __('Unknown')
        ]);
        return view('agent::admin.settings.edit', compact('agent', 'title'));
    }


    public function settingsStore(Request $request)
    {
        $data = $request->validate([
            'currency_id'         => 'required|exists:currencies,id',
            'type' => [
                'required', 'string',
                Rule::in(['cash_in', 'cash_out']),
                Rule::unique('agent_settings')->where(fn($q) =>
                $q->where('currency_id', $request->currency_id)
                ),
            ],
            'min_limit'           => 'required|numeric|min:0',
            'max_limit'           => 'required|numeric|min:0',
            'fixed_charge'        => 'required|numeric|min:0',
            'percent_charge'      => 'required|numeric|min:0',
            'fixed_commission'    => 'required|numeric|min:0',
            'percent_commission'  => 'required|numeric|min:0',
            'daily_limit'         => 'required|numeric|min:0',
            'monthly_limit'       => 'required|numeric|min:0',
            'status'              => 'nullable|boolean',
        ], [
            'type.unique' => __('Agent settings for this type and currency already exist.'),
        ]);

        AgentSetting::create($data);

        return to_route('admin.agent.settings')
            ->with('success', __('Agent settings for :type created successfully.', [
                'type' => Str::headline($request->type)
            ]));
    }



    public function updateSettings(Request $request,$id)
    {
        $agentSetting = AgentSetting::query()->findOrFail($id);

        $data = $request->validate([
            'currency_id'         => 'required|exists:currencies,id',
            'type'                => 'required|string|in:cash_in,cash_out',
            'min_limit'           => 'required|numeric|min:0',
            'max_limit'           => 'required|numeric|min:0',
            'fixed_charge'        => 'required|numeric|min:0',
            'percent_charge'      => 'required|numeric|min:0',
            'fixed_commission'    => 'required|numeric|min:0',
            'percent_commission'  => 'required|numeric|min:0',
            'daily_limit'         => 'required|numeric|min:0',
            'monthly_limit'       => 'required|numeric|min:0',
            'status'              => 'nullable|boolean',
        ]);

        $agentSetting->update($data);

        return back()->with('success', __('Agent settings for :type (:currency) updated successfully.', [
            'type' => Str::headline($agentSetting->type),
            'currency' => $agentSetting->currency->code
        ]));
    }



    public function agentIndex($state='all')
    {
        $data['basic'] = basicControl();
        $data['currencies'] = Currency::select(['id', 'code', 'name'])->where('is_active', 1)->get();
        $userRecord = \Cache::get('agentRecord');
        if (!$userRecord) {
            $userRecord = User::withTrashed()
                ->where('type','agent')
                ->selectRaw('COUNT(id) AS totalUserWithTrashed')
                ->selectRaw('COUNT(CASE WHEN deleted_at IS NULL THEN id END) AS totalUser')
                ->selectRaw('(COUNT(CASE WHEN deleted_at IS NULL THEN id END) / COUNT(id)) * 100 AS totalUserPercentage')
                ->selectRaw('COUNT(CASE WHEN status = 1 THEN id END) AS activeUser')
                ->selectRaw('(COUNT(CASE WHEN status = 1 THEN id END) / COUNT(id)) * 100 AS activeUserPercentage')
                ->selectRaw('COUNT(CASE WHEN DATE(created_at) = CURDATE() THEN id END) AS todayJoin')
                ->selectRaw('(COUNT(CASE WHEN DATE(created_at) = CURDATE() THEN id END) / COUNT(id)) * 100 AS todayJoinPercentage')
                ->selectRaw('COUNT(CASE WHEN status = 0 THEN id END) AS deactivateUser')
                ->selectRaw('(COUNT(CASE WHEN status = 0 THEN id END) / COUNT(id)) * 100 AS deactivateUserPercentage')
                ->get()
                ->toArray();
            \Cache::put('agentRecord', $userRecord);
        }
        $userRecord = collect($userRecord)->collapse();
        $data['languages'] = Language::all();
        $data['allCountry'] = config('country');
        return view('agent::admin.agent_management.index', $data, compact('userRecord','state'));

    }

    public function agentIndexSearch(Request $request, $state = null)
    {
        $search = $request->search['value'];
        $filterStatus = $request->filterStatus;
        $filterName = $request->filterName;
        $filterEmailVerification = $request->filterEmailVerification;
        $filterSMSVerification = $request->filterSMSVerification;
        $filterTwoFaSecurity = $request->filterTwoFaVerification;

        $users = User::query()
            ->where('type','agent')
            ->filterByState($state)
            ->orderBy('id', 'DESC')
            ->when(!empty($search), function ($query) use ($search) {
                return $query->where('email', 'LIKE', "%{$search}%")
                    ->orWhere('username', 'LIKE', "%{$search}%")
                    ->orWhere('firstname', 'LIKE', "%{$search}%")
                    ->orWhere('lastname', 'LIKE', "%{$search}%")
                    ->orWhere('phone', 'LIKE', "%{$search}%");
            })
            ->when(isset($filterName) && !empty($filterName), function ($query) use ($filterName) {
                return $query->where('username', 'LIKE', "%{$filterName}%")
                    ->orWhere('firstname', 'LIKE', "%{$filterName}%")
                    ->orWhere('lastname', 'LIKE', "%{$filterName}%");
            })
            ->when(isset($filterStatus), function ($query) use ($filterStatus) {
                if ($filterStatus == 'all') {
                    return $query->where('status', '!=', null);
                }
                return $query->where('status', $filterStatus);
            })
            ->when(isset($filterEmailVerification) && !empty($filterEmailVerification), function ($query) use ($filterEmailVerification) {
                return $query->where('email_verification', $filterEmailVerification);
            })
            ->when(isset($filterSMSVerification) && !empty($filterSMSVerification), function ($query) use ($filterSMSVerification) {
                return $query->where('sms_verification', $filterSMSVerification);
            })
            ->when(isset($filterTwoFaSecurity) && !empty($filterTwoFaSecurity), function ($query) use ($filterTwoFaSecurity) {
                return $query->where('two_fa_verify', $filterTwoFaSecurity);
            });

        return DataTables::of($users)
            ->addColumn('checkbox', function ($item) {
                return '<input type="checkbox" id="chk-' . $item->id . '"
                           class="form-check-input row-tic tic-check" name="check" value="' . $item->id . '"
                           data-id="' . $item->id . '">';
            })
            ->addColumn('name', function ($item) {
                $url = route('admin.user.view.profile', $item->id);
                return '<a class="d-flex align-items-center me-2" href="' . $url . '">
                            <div class="flex-shrink-0">
                              ' . $item->profilePicture() . '
                            </div>
                            <div class="flex-grow-1 ms-3">
                              <h5 class="text-hover-primary mb-0">' . $item->name . '</h5>
                              <span class="fs-6 text-body">@' . $item->username . '</span>
                            </div>
                        </a>';

            })
            ->addColumn('email-phone', function ($item) {
                return '<span class="d-block h5 mb-0">' . $item->email . '</span>
                            <span class="d-block fs-5">' . $item->phone . '</span>';
            })
            ->addColumn('country', function ($item) {
                return $item->country ?? 'N/A';
            })
            ->addColumn('status', function ($item) {
                if ($item->status == 1) {
                    return '<span class="badge bg-soft-success text-success">
                    <span class="legend-indicator bg-success"></span>' . trans('Active') . '
                  </span>';

                } else {
                    return '<span class="badge bg-soft-danger text-danger">
                    <span class="legend-indicator bg-danger"></span>' . trans('Inactive') . '
                  </span>';
                }
            })
            ->addColumn('last login', function ($item) {
                return diffForHumans($item->last_login);
            })
            ->addColumn('action', function ($item) {
                $editUrl = route('admin.user.edit', $item->id);
                $viewProfile = route('admin.user.view.profile', $item->id);
                return '<div class="btn-group" role="group">
                      <a href="' . $editUrl . '" class="btn btn-white btn-sm edit_user_btn">
                        <i class="bi-pencil-square me-1"></i> ' . trans("Edit") . '
                      </a>
                    <div class="btn-group">
                      <button type="button" class="btn btn-white btn-icon btn-sm dropdown-toggle dropdown-toggle-empty" id="userEditDropdown" data-bs-toggle="dropdown" aria-expanded="false"></button>
                      <div class="dropdown-menu dropdown-menu-end mt-1" aria-labelledby="userEditDropdown">
                       <a class="dropdown-item" href="' . $viewProfile . '">
                          <i class="bi-eye dropdown-item-icon"></i> ' . trans("View Profile") . '
                        </a>
                          <a class="dropdown-item" href="' . route('admin.send.email', $item->id) . '"> <i
                                class="bi-envelope dropdown-item-icon"></i> ' . trans("Send Mail") . ' </a>
                          <a class="dropdown-item loginAccount" href="javascript:void(0)"
                           data-route="' . route('admin.login.as.user', $item->id) . '"
                           data-bs-toggle="modal" data-bs-target="#loginAsUserModal">
                            <i class="bi bi-box-arrow-in-right dropdown-item-icon"></i>
                           ' . trans("Login As Agent") . '
                        </a>
                         <a class="dropdown-item addBalance" href="javascript:void(0)"
                           data-route="' . route('admin.user.update.balance', $item->id) . '"
                           data-id="' . $item->id . '"
                           data-bs-toggle="modal" data-bs-target="#addBalanceModal">
                            <i class="bi bi-cash-coin dropdown-item-icon"></i>
                            ' . trans("Manage Balance") . '
                        </a>
                      </div>
                    </div>
                  </div>';
            })->rawColumns(['action', 'checkbox', 'name', 'email-phone', 'status'])
            ->make(true);
    }



}
