@extends('admin.layouts.app')
@section('page_title', __('Referral Bonus'))

@section('content')

    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-sm mb-2 mb-sm-0">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb breadcrumb-no-gutter">
                            <li class="breadcrumb-item">
                                <a class="breadcrumb-link" href="javascript:void(0)">@lang("Dashboard")</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">@lang('Referral Settings')</li>
                        </ol>
                    </nav>
                    <h1 class="page-header-title">@lang('Referral Bonus')</h1>
                </div>
            </div>
        </div>

        <div class="row justify-content-md-center">
            <div class="col-lg-12">
                <div class="card mb-4 card-primary shadow">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h4 class="card-header-title">@lang('Bonus on Add Fund')</h4>
                    </div>

                    <div class="card-body parent">
                        <table class="table table-responsive">
                            <thead class="thead-light">
                            <tr>
                                <th>@lang('Level')</th>
                                <th>@lang('Commission Amount')</th>
                                <th>@lang('Minimum Amount')</th>
                                <th>@lang('Status')</th>
                                <th>@lang('Type')</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($referralBonuses->where('referral_on','deposit') as $k => $v)
                                <tr>
                                    <td data-label="@lang('Level')">
                                        <div class="avatar avatar-sm avatar-soft-dark avatar-circle">
                                            <span class="avatar-initials">{{ __($v->level) }}</span>
                                        </div>
                                    </td>
                                    <td data-label="@lang('Commission')" class="text-success fw-semibold">
                                        {{ (currencyPosition($v->amount)) }}
                                    </td>
                                    <td data-label="@lang('Minimum')" class="text-info fw-semibold">
                                        {{ (currencyPosition($v->minAmount)) }}
                                    </td>
                                    <td data-label="@lang('Status')">
                                        {!! $v->getStatus() !!}
                                    </td>
                                    <td data-label="Type">
                                        {!! $v->getType() !!}
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        <hr>
                        <div class="row mt-3 mb-5">
                            <div class="col-md-6 mt-2">
                                <input type="number" name="level" value="1"
                                       placeholder="@lang('HOW MANY LEVEL')"
                                       class="form-control levelGenerate">
                            </div>
                            <div class="col-md-6 mt-2">
                                <button type="button" class="btn btn-primary btn-block generate">
                                    @lang('GENERATE')
                                </button>
                            </div>
                        </div>
                        <form action="{{route('admin.referral.bonus.index')}}" method="post">
                            @csrf
                            <input type="hidden" name="referral_on" class="referral_on" value="deposit">
                            <div class="d-none levelForm">
                                <div class="form-group">
                                    <label class="text-success"> @lang('Level & Commission :')
                                        <small>@lang('(Old Levels will Remove After Generate)')</small>
                                    </label>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="description referral-desc">
                                                <div class="row">
                                                    <div class="col-md-12 planDescriptionContainer">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-sm btn-primary btn-block my-3">
                                    @lang('Save Changes')
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection


@push('script')
    <script>
        "use strict";
        $(document).ready(function () {
            $(document).on('click', '.generate', function () {
                let levelGenerate = $(this).parents('.parent').find('.levelGenerate').val();
                let referral_on = $(this).parents('.parent').find('.referral_on').val();
                let a = 0;
                let val = 1;
                let viewHtml = '';
                if (levelGenerate !== '' && levelGenerate > 0) {
                    $(this).parents('.parent').find('.levelForm').removeClass('d-none');
                    $(this).parents('.parent').find('.levelForm').addClass('d-block');
                    for (a; a < parseInt(levelGenerate); a++) {
                        viewHtml += `<div class="input-group input-group-sm mt-4">
                            <div class="input-group-prepend">
                                <span class="input-group-text no-right-border">@lang('LEVEL')</span>
                            </div>
                            <input name="level[]" class="form-control margin-top-10 no-left-border width-120" type="number" readonly value="${val++}" required placeholder="@lang('Level')">
                            <input name="amount[]" class="form-control margin-top-10" type="text" required placeholder="@lang('Amount')">

                          	<select name="status[]" class="form-control form-control-sm form-select">
                          	<option value="1">@lang('Active')</option>
                          	<option value="0">@lang('Deactive')</option>
							</select>
							${(referral_on !== 'login') ?
                            `<input name="minAmount[]" class="form-control margin-top-10" type="text" required placeholder="@lang('Min Amount')">
								<select name="calcType[]" class="form-control form-control-sm form-select">
									<option value="1">@lang('Fixed')</option>
									<option value="0">@lang('Percent')</option>
								</select>` : `<input type="hidden" name="calcType[]" value="1"><input type="hidden" name="minAmount[]" value="0">`}

							<button class="btn btn-danger input-group-text delete_desc" type="button"><i class='bi bi-x'></i></button>
							</div>`;
                    }
                    $(this).parents('.parent').find('.planDescriptionContainer').html(viewHtml);
                } else {
                    Notiflix.Notify.failure('Level Field Is Required');
                }
            });

            $(document).on('click', '.delete_desc', function () {
                $(this).closest('.input-group').remove();
            });
        });
    </script>
@endpush
