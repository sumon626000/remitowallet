@extends('admin.layouts.app')
@section('page_title',__('Bill Service List'))

@section('content')
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title">@yield('page_title')</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb breadcrumb-no-gutter">
                            <li class="breadcrumb-item">
                                <a class="breadcrumb-link" href="{{ route('admin.dashboard') }}">@lang("Dashboard")</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">@yield('page_title')</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title m-0">@lang('Bill Services')</h4>
                <div class="d-flex justify-content-end gap-2">
                    <a href="#" class="btn btn-primary btn-sm bulkAdd " data-bs-target="#limit_charge"
                       data-route="{{route('admin.bill.chargeLimit.add')}}"
                       data-bs-toggle="modal"><i class="fa-light fa-bullhorn"></i> @lang('Add Bulk Limit and Charge')</a>
                    @include('admin.bill_payment.searchForm')
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-thead-bordered table-nowrap table-align-middle card-table">
                    <thead class="thead-light">
                    <tr>
                        <th>@lang('SL.')</th>
                        <th>@lang('Type')</th>
                        <th>@lang('Code')</th>
                        <th>@lang('Category')</th>
                        <th>@lang('Country')</th>
                        <th>@lang('Charges')</th>
                        <th>@lang('Limit')</th>
                        <th>@lang('Status')</th>
                        <th>@lang('Action')</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($services as $key => $value)
                        <tr>
                            <td data-label="@lang('SL.')">{{ ++$key }} </td>
                            <td data-label="@lang('Type')">{{ __($value->type) }}</td>
                            <td data-label="@lang('Code')">{{ __($value->code) }}</td>
                            <td data-label="@lang('Name')">{{str_replace('_',' ',ucfirst($value->service)) }}</td>
                            <td data-label="@lang('Country')">{{ __(getCountryName($value->country)) }}</td>
                            <td data-label="@lang('Charges')"><span class="text-danger">{{$value->percent_charge}}%
															+ {{$value->fixed_charge}} {{$value->currency}}</span></td>
                            <td data-label="@lang('Limit')"><span
                                    class="text-dark font-weight-bold">{{$value->min_amount}}
															- {{$value->max_amount}} {{$value->currency}}</span></td>
                            <td data-label="@lang('Status')">
                                @if($value->status)
                                    <span class="badge bg-soft-success text-success">
                                        <span class="legend-indicator bg-success"></span>@lang('Active')
                                    </span>
                                @else
                                    <span class="badge bg-soft-danger text-danger">
                                        <span class="legend-indicator bg-danger"></span>@lang('InActive')
                                    </span>
                                @endif
                            </td>
                            <td data-label="@lang('Action')">
                                <a href="javascript:void(0)"
                                   class="btn btn-white btn-sm editCharge"
                                   data-bs-target="#limit_charge_edit"
                                   data-bs-toggle="modal"
                                   data-resource="{{json_encode($value)}}"
                                   data-re="{{json_encode($countryList)}}"
                                   data-route="{{route('admin.bill.chargeLimit.edit',$value->id)}}"><i
                                        class="fa-light fa-edit"></i></a>
                                @if($value->status == 0)
                                    <a href="javascript:void(0)"
                                       class="btn btn-white btn-sm enableStatus"
                                       data-bs-target="#status_change"
                                       data-bs-toggle="modal"
                                       title="@lang('Enable')"
                                       data-route="{{route('admin.bill.status.change',$value->id)}}"><i
                                            class="fa-light fa-check"></i></a>
                                @else
                                    <a href="javascript:void(0)"
                                       class="btn btn-white btn-sm disableStatus"
                                       data-bs-target="#status_change"
                                       data-bs-toggle="modal"
                                       title="@lang('Disable')"
                                       data-route="{{route('admin.bill.status.change',$value->id)}}"><i
                                            class="fa-light fa-times"></i></a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <td colspan="5">
                            <div class="text-center p-4">
                                <img class="dataTables-image mb-3" src="{{ asset('assets/admin/img/oc-error.svg') }}"
                                     alt="Image Description" data-hs-theme-appearance="default">
                                <img class="dataTables-image mb-3"
                                     src="{{ asset('assets/admin/img/oc-error-light.svg') }}" alt="Image Description"
                                     data-hs-theme-appearance="dark">
                                <p class="mb-0">@lang('No data to show')</p>
                            </div>
                        </td>
                    @endforelse
                    </tbody>
                </table>
                <div class="card-footer">
                    {{ $services->appends($_GET)->links($theme.'partials.pagination') }}
                </div>
            </div>
        </div>
    </div>

    {{-- Status Change --}}
    <div id="status_change" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="primary-header-modalLabel"
         aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title text-dark font-weight-bold"
                        id="primary-header-modalLabel">@lang('Status Change Confirmation')</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true"></button>
                </div>
                <form action="" method="post" class="statusRoute">
                    @csrf
                    <div class="modal-body">
                        <div id="tag-body">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-dark" data-bs-dismiss="modal">@lang('Close')</button>
                        <button type="submit" class="btn btn-primary">@lang('Yes')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- charge limit bulk add --}}
    <div id="limit_charge" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="primary-header-modalLabel"
         aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title text-dark font-weight-bold"
                        id="primary-header-modalLabel">@lang('Add Bulk Limit and Charges')</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true"></button>
                </div>
                <form action="" method="post" class="formRoute">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <label>@lang('Service')</label>
                                <div class="tom-select-custom tom-select-custom-with-tags mt-2">
                                    <select class="js-select form-select selectService" name="service" required
                                            data-hs-tom-select-options='{
                                        "placeholder": "Select service..."
                                      }'>
                                        @if($categories)
                                            <option value="">@lang('Select Service')</option>
                                            @foreach($categories as $category)
                                                <option
                                                    value="{{$category->service}}">{{str_replace('_',' ',ucfirst($category->service))}}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                                @error('service')
                                <span class="text-danger">{{$message}}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <label>@lang('Country')</label>
                                <div class="tom-select-custom tom-select-custom-with-tags mt-2">
                                    <select class="js-select form-select changeCountry" name="country"
                                            data-re="{{json_encode($countryList)}}" required
                                            data-hs-tom-select-options='{
                                        "placeholder": "Select Country..."
                                      }'>
                                        <option value="">@lang('Select Country')</option>
                                        @if($countries)
                                            @foreach($countries as $country)
                                                <option value="{{$country->country}}">{{$country->countryName}}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                                @error('country')
                                <span class="text-danger">{{$message}}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <label>@lang('Percent Charge')</label>
                                <div class="input-group">
                                    <input type="number" step="0.001" name="percent_charge" class="form-control"
                                           required>
                                    <div class="input-group-append">
                                        <span class="form-control">%</span>
                                    </div>
                                </div>
                                @error('percent_charge')
                                <span class="text-danger">{{$message}}</span>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label>@lang('Fixed Charge')</label>
                                <div class="input-group">
                                    <input type="number" step="0.001" name="fixed_charge" class="form-control" required>
                                    <div class="input-group-append">
                                        <span class="form-control showCurrency"></span>
                                    </div>
                                </div>
                                @error('fixed_charge')
                                <span class="text-danger">{{$message}}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <label>@lang('Minimum Amount')</label>
                                <div class="input-group">
                                    <input type="number" step="0.001" name="min_amount" class="form-control">
                                    <div class="input-group-append">
                                        <span class="form-control showCurrency"></span>
                                    </div>
                                </div>
                                @error('min_amount')
                                <span class="text-danger">{{$message}}</span>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label>@lang('Maximum Amount')</label>
                                <div class="input-group">
                                    <input type="number" step="0.001" name="max_amount" class="form-control">
                                    <div class="input-group-append">
                                        <span class="form-control showCurrency"></span>
                                    </div>
                                </div>
                                @error('max_amount')
                                <span class="text-danger">{{$message}}</span>
                                @enderror
                            </div>
                            <input type="hidden" name="currency" class="hdCurrency" value="">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-dark" data-bs-dismiss="modal">@lang('Close')</button>
                        <button type="submit" class="btn btn-primary">@lang('Add')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- charge limit edit --}}
    <div id="limit_charge_edit" class="modal fade" tabindex="-1" role="dialog"
         aria-labelledby="primary-header-modalLabel"
         aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title text-dark font-weight-bold"
                        id="primary-header-modalLabel">@lang('Edit Limit and Charges')</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true"></button>
                </div>
                <form action="" method="post" class="formRouteEdit">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <label>@lang('Select Service')</label>
                                <select class="form-control showService" name="service">
                                </select>
                                @error('service')
                                <span class="text-danger">{{$message}}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <label>@lang('Select Country')</label>
                                <select class="form-control showCountry" name="country">
                                </select>
                                @error('country')
                                <span class="text-danger">{{$message}}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <label>@lang('Percent Charge')</label>
                                <div class="input-group">
                                    <input type="number" step="0.001" name="percent_charge" class="form-control percent"
                                           required>
                                    <div class="input-group-append">
                                        <span class="form-control">%</span>
                                    </div>
                                </div>
                                @error('percent_charge')
                                <span class="text-danger">{{$message}}</span>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label>@lang('Fixed Charge')</label>
                                <div class="input-group">
                                    <input type="number" step="0.001" name="fixed_charge" class="form-control fix"
                                           required>
                                    <div class="input-group-append">
                                        <span class="form-control showCurrency"></span>
                                    </div>
                                </div>
                                @error('fixed_charge')
                                <span class="text-danger">{{$message}}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <label>@lang('Minimum Amount')</label>
                                <div class="input-group">
                                    <input type="number" step="0.001" name="min_amount" class="form-control min">
                                    <div class="input-group-append">
                                        <span class="form-control showCurrency"></span>
                                    </div>
                                </div>
                                @error('min_amount')
                                <span class="text-danger">{{$message}}</span>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label>@lang('Maximum Amount')</label>
                                <div class="input-group">
                                    <input type="number" step="0.001" name="max_amount" class="form-control max">
                                    <div class="input-group-append">
                                        <span class="form-control showCurrency"></span>
                                    </div>
                                </div>
                                @error('max_amount')
                                <span class="text-danger">{{$message}}</span>
                                @enderror
                            </div>
                            <input type="hidden" name="currency" class="hdCurrency" value="">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-dark" data-bs-dismiss="modal">@lang('Close')</button>
                        <button type="submit" class="btn btn-primary">@lang('Add')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('css-lib')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/tom-select.bootstrap5.css') }}">
@endpush

@push('js-lib')
    <script src="{{ asset('assets/admin/js/tom-select.complete.min.js') }}"></script>
@endpush

@push('script')
    <script>
        'use strict';

        $(document).on('click', '.enableStatus', function () {
            $('#tag-body').html('');
            var route = $(this).data('route');
            $('.statusRoute').attr('action', route)
            $('#tag-body').append(`<p>Are you sure enable the service<p>`)
        });

        $(document).on('click', '.disableStatus', function () {
            $('#tag-body').html('');
            var route = $(this).data('route');
            $('.statusRoute').attr('action', route)
            $('#tag-body').append(`<p>Are you sure disable the service<p>`)
        });

        $(document).on('click', '.editCharge', function () {
            var route = $(this).data('route');
            var resource = $(this).data('resource');
            $('.formRouteEdit').attr('action', route)
            $('.showCountry').html('')
            $('.showService').html('')
            $('.percent').val(resource.percent_charge)
            $('.fix').val(resource.fixed_charge)
            $('.min').val(resource.min_amount)
            $('.max').val(resource.max_amount)
            $('.showCurrency').text(resource.currency)
            let replaceService = resource.service.replace('_', ' ')
            $('.showCountry').append(`<option value="${resource.country}">${resource.countryName}</option>`);
            $('.showService').append(`<option value="${resource.service}">${replaceService}</option>`);
            var country = $(this).data('re');
            currencCode(country, resource.country);
        });

        $(document).on('click', '.bulkAdd', function () {
            var route = $(this).data('route');
            $('.formRoute').attr('action', route)
        });

        $(document).on('change', '.changeCountry', function () {
            var country = $(this).data('re');
            var code = $(this).find(':selected').val()

            currencCode(country, code);
        })

        function currencCode(country, code) {
            Object.keys(country).forEach(key => {
                let singleCode = country[key].code;
                if (singleCode == code) {
                    $('.showCurrency').text(country[key].iso_code)
                    $('.hdCurrency').val(country[key].iso_code)
                    return;
                }
            });
        }

        HSCore.components.HSTomSelect.init('.js-select', {
            maxOptions: 500
        })
    </script>
@endpush
