@extends('admin.layouts.app')
@section('page_title',__('Card Transactions'))

@section('content')
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title">@yield('page_title')</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb breadcrumb-no-gutter">
                            <li class="breadcrumb-item"><a class="breadcrumb-link"
                                                           href="{{ route('admin.dashboard') }}">@lang("Dashboard")</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">@lang('Virtual Card')</li>
                            <li class="breadcrumb-item active" aria-current="page">@yield('page_title')</li>

                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title m-0">@lang('Card Transactions')</h4>
                <a href="{{route('admin.virtual.cardList','all')}}"
                   class="btn btn-primary"><i class="bi-arrow-left"></i> @lang('Back')</a>
            </div>

            <div class="table-responsive">
                <table class="table table-thead-bordered table-nowrap table-align-middle card-table">
                    <thead class="thead-light">
                    <tr>
                        <th>@lang('SL.')</th>
                        <th>@lang('User')</th>
                        <th>@lang('Provider')</th>
                        <th>@lang('Amount')</th>
                        <th scope="col">@lang('More')</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($transactions as $key => $item)
                        <tr>
                            <td data-label="@lang('SL.')">{{ ++$key }} </td>
                            <td data-label="@lang('User')">
                                <a href="{{ route('user.edit', $item->user_id)}}"
                                   class="text-decoration-none">
                                    <div class="d-lg-flex d-block align-items-center ">
                                        <div class="mr-3"><img
                                                src="{{ optional($item->user)->profilePicture()??asset('assets/upload/boy.png') }}"
                                                alt="user"
                                                class="rounded-circle" width="35"
                                                data-bs-toggle="tooltip" title=""
                                                data-original-title="{{optional($item->user)->name?? __('N/A')}}">
                                        </div>
                                        <div
                                            class="d-inline-flex d-lg-block align-items-center">
                                            <p class="text-dark mb-0 font-16 font-weight-medium">{{Str::limit(optional($item->user)->name?? __('N/A'),20)}}</p>
                                            <span
                                                class="text-muted font-14 ml-1">{{ '@'.optional($item->user)->username?? __('N/A')}}</span>
                                        </div>
                                    </div>
                                </a>
                            </td>
                            <td data-label="@lang('Provider')">{{optional($item->cardOrder->cardMethod)->name}}</td>
                            <td data-label="@lang('Amount')">{{$item->amount}} {{$item->currency_code}}</td>
                            <td data-label="@lang('More')">
                                <a href="javascript:void(0)"
                                   class="btn btn-white btn-sm details"
                                   title="view" data-bs-target="#viewDetails"
                                   data-bs-toggle="modal"
                                   data-resource="{{json_encode($item->data)}}"><i
                                        class="bi-eye"></i></a>
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
                    {{ $transactions->appends($_GET)->links($theme.'partials.pagination') }}
                </div>
            </div>
        </div>
    </div>
    <div id="viewDetails" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="primary-header-modalLabel"
         aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title text-dark font-weight-bold"
                        id="primary-header-modalLabel">@lang('Transaction Information')</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true"></button>
                </div>
                <div class="modal-body">
                    <div class="tranShow">

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-dark" data-bs-dismiss="modal">@lang('Close')</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        'use strict'
        $(document).on('click', '.details', function () {
            $('.tranShow').html('');
            var res = $(this).data('resource');
            for (const key in res) {
                let newKey = key.replace('_', ' ');
                let finalKey = newKey.toUpperCase();
                var list = `<li class="list-group-item d-flex justify-content-between text-dark font-weight-bold">
													<span>${finalKey}</span>
													<span
														class="text-dark font-weight-bold">${res[key]}</span>
												</li>`

                $('.tranShow').append(list);
            }

        })
    </script>
@endpush
