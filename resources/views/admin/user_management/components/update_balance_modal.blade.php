<!-- Modal -->
@php
    $currencies = \App\Models\Currency::where('is_active',1)->get();
@endphp

<div class="modal fade" id="addBalanceModal" tabindex="-1" role="dialog" aria-labelledby="addBalanceModalLabel"
     data-bs-backdrop="static"
     aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="addBalanceModalLabel"><i
                        class="fa-light fa-square-check"></i> @lang('Manage Balance')</h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="post" class="setBalanceRoute" id="form">
                @csrf
                <div class="modal-body">
                    <ul class="list-group mb-4" id="showBalance">

                    </ul>

                    <div>
                        <!-- Select -->
                        <label class="form-label" for="exampleFormControlInput1">@lang("Select Currency")</label>
                        <div class="tom-select-custom">
                            <select class="js-select form-select" name="currency_id" autocomplete="off"
                                    data-hs-tom-select-options='{
                            "placeholder": "Select currency..."
                          }'>
                                @if(!empty($currencies))
                                    @foreach($currencies as $currency)
                                        <option value="{{$currency->id}}">{{$currency->name}}
                                            - {{$currency->code}}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <!-- End Select -->
                    </div>

                    <div class="mt-3">
                        <label class="form-label" for="exampleFormControlInput1">@lang("Enter Amount")</label>
                        <input type="text" class="form-control" name="amount" placeholder="@lang("Enter Amount")"
                               value="{{ old("amount") }}"
                               autocomplete="off">
                        @error('amount')
                        <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="input-group input-group-sm-vertical mt-3">
                        <label class="form-control" for="editUserModalAccountTypeModalRadioEg1_2">
                            <span class="form-check">
                              <input type="radio" class="form-check-input" name="balance_operation"
                                     id="editUserModalAccountTypeModalRadioEg1_2" value="1" checked>
                              <span class="form-check-label"><i class="bi bi-plus"></i> @lang("Add Balance")</span>
                            </span>
                        </label>

                        <label class="form-control" for="editUserModalAccountTypeModalRadioEg1_1">
                        <span class="form-check">
                          <input type="radio" class="form-check-input" name="balance_operation"
                                 id="editUserModalAccountTypeModalRadioEg1_1" value="0">
                          <span class="form-check-label"><i class="bi bi-dash me"></i> @lang("Subtract Balance")</span>
                        </span>
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-white" data-bs-dismiss="modal">@lang('Close')</button>
                    <button type="submit" class="btn btn-primary" id="submitBtn">@lang('Confirm')</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- End Modal -->


@push('script')
    <script>
        $(document).ready(function () {
            $('input[name="balance_operation"]').on('click', function () {
                $('input[name="balance_operation"]').not(this).prop('checked', false);
            });
        });

        $('#submitBtn').click(function () {
            $(this).prop('disabled', true);
            $('#form').submit();
        });

    </script>

@endpush
