@extends(userLayout())
@section('title', trans('Insert Redeem Code'))
@section('content')
    <div class="content {{ containerClass() }}">
        <x-page-header menu="Services" submenu="Redeem" pagetitle="Insert Redeem Code"/>

        <div class="row d-flex justify-content-center">
            <div class="col-lg-8 mb-3 mb-lg-0">
                <div class="card mb-3 mb-lg-5">
                    <div class="card-header">
                        <h4 class="card-header-title">@lang('Insert Redeem Code')</h4>
                    </div>
                    <form action="{{ route('user.redeem.insert') }}" method="post">
                        @csrf
                        <div class="card-body">
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label for="redeemCode" class="form-label">@lang('Redeem Code')</label>
                                    <input type="text" class="form-control" name="redeemCode" id="redeemCode"
                                           placeholder="@lang('xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx')">
                                    <small class="form-text"></small>
                                    @error('redeemCode')
                                    <span class="text-danger">{{$message}}</span>
                                    @enderror
                                </div>
                            </div>

                            <button id="submit" type="submit"
                                    class="btn btn-primary mt-4" disabled>@lang('Submit redeem code')</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        'use strict';
        $(document).ready(function () {
            let redeemCodeField = $('#redeemCode');
            let redeemCodeStatus = false;

            function clearMessage(fieldId) {
                $(fieldId).removeClass('is-valid')
                $(fieldId).removeClass('is-invalid')
                $(fieldId).siblings('small').html('');
            }

            $(document).on('input', "#redeemCode", function (e) {
                let redeemCode = redeemCodeField.val();

                clearMessage(redeemCodeField);
                $(redeemCodeField).addClass('is-valid');
                $(redeemCodeField).closest('div').find(".valid-feedback").html('okay');
                redeemCodeStatus = true;
                submitButton();
            });

            function submitButton() {
                if (redeemCodeStatus) {
                    $("#submit").removeAttr("disabled");
                } else {
                    $("#submit").attr("disabled", true);
                }
            }
        })
    </script>
@endpush
