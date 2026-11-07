@extends(userLayout())
@section('title',__('Business Api key'))

@section('content')

    <div class="content {{ containerClass() }}" id="api-app">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-sm mb-2 mb-sm-0">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb breadcrumb-no-gutter">
                            <li class="breadcrumb-item">
                                <a class="breadcrumb-link" href="{{route('merchant.dashboard')}}">@lang("Dashboard")</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">@lang('Settings')</li>
                        </ol>
                    </nav>
                    <h1 class="page-header-title">@lang('Business Api key')</h1>
                </div>
            </div>
        </div>

        <div class="row d-flex justify-content-center">
            <div class="col-md-12">
                <div class="d-grid gap-3 gap-lg-5">
                    <div class="card">
                        <div class="card-header card-header-content-md-between">
                            <h4 class="card-header-title">@lang('Business Api key')</h4>
                            <div class="col-md-4">
                                <div class="input-group input-group-sm-vertical">
                                    <a href="{{ route('merchant.mode.change', 'test') }}" class="form-control d-flex align-items-center">
                                        <input type="radio" class="form-check-input me-2" {{ $merchant->mode == 0 ? 'checked' : '' }}>
                                        <span>@lang('Test Mode')</span>
                                    </a>
                                    <a href="{{ route('merchant.mode.change', 'live') }}" class="form-control d-flex align-items-center">
                                        <input type="radio" class="form-check-input me-2" {{ $merchant->mode == 1 ? 'checked' : '' }}>
                                        <span>@lang('Live Mode')</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <form>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label" for="Public Key">@lang('Public Key:')</label>
                                            <div class="input-group">
                                                <input type="text" name="public_key" class="form-control"
                                                       value="{{$merchant->public_key}}" readonly>
                                                    <button type="button" class="btn btn-info input-group-text gap-1"
                                                            @click="copyPublicCode()">
                                                        <i class="bi-clipboard"></i> @lang('Copy')
                                                    </button>
                                            </div>
                                            <div class="invalid-feedback">
                                                @error('public_key') @lang($message) @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label" for="Public Key">@lang('Secret Key:')</label>
                                            <div class="input-group">
                                                <input type="text" name="secret_key" class="form-control"
                                                       value="{{$merchant->secret_key}}" readonly>
                                                    <button type="button" class="btn btn-info input-group-text gap-1"
                                                            @click="copySecretCode()">
                                                        <i class="bi-clipboard"></i> @lang('Copy')
                                                    </button>
                                            </div>
                                            <div class="invalid-feedback">
                                                @error('secret_key') @lang($message) @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-primary  btn-block mt-4"
                                        data-bs-target="#generate" data-bs-toggle="modal">@lang('Generate Api Key')
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

	<div id="generate" class="modal fade" tabindex="-1" role="dialog"
		 aria-labelledby="primary-header-modalLabel"
		 aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title text-dark font-weight-bold"
						id="primary-header-modalLabel">@lang('Re-Generate Confirmation ')</h4>
					<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
				</div>
				<form action="{{route('merchant.api.key')}}" method="post">
					@csrf
					<div class="modal-body">
						<h5>@lang('Are you sure want generate new api key?')</h5>
						<p>@lang('It may cause interrupt with your existing api request operations.')</p>
						<div class="form-group mt-3 security-block">
							<label for="security_pin">@lang('Security Pin')</label>
							<input type="password" name="security_pin"
								   placeholder="@lang('Please enter your security PIN')"
								   autocomplete="off" value="{{ old('security_pin') }}"
								   class="form-control @error('security_pin') is-invalid @enderror">
							<div class="invalid-feedback">
								@error('security_pin') @lang($message) @enderror
							</div>
							<div class="valid-feedback"></div>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-white" data-bs-dismiss="modal">@lang('Close')</button>
						<button type="submit" class="btn btn-primary">@lang('Confirm')</button>
					</div>
				</form>
			</div>
		</div>
	</div>

@endsection


@push('script')
	<script>
		'use script'
		var newApp = new Vue({
			el: "#api-app",
			data: {
				item: {
					public_key: '{{$merchant->public_key}}',
					secret_key: '{{$merchant->secret_key}}',
				},
			},
			mounted() {
			},
			methods: {
				copyPublicCode() {
					let text = this.item.public_key;
					navigator.clipboard.writeText(text);
					Notiflix.Notify.success(`Copied: ${text}`);
				},
				copySecretCode() {
					let text = this.item.secret_key;
					navigator.clipboard.writeText(text);
					Notiflix.Notify.success(`Copied: ${text}`);
				},
			},
		})
	</script>
	@if ($errors->any())
		@php
			$collection = collect($errors->all());
			$errors = $collection->unique();
		@endphp
		<script>
			"use strict";
			@foreach ($errors as $error)
			Notiflix.Notify.failure("{{ trans($error) }}");
			@endforeach
		</script>
	@endif
@endpush
