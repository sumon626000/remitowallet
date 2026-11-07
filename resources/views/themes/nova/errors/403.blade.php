@extends(template().'layouts.error')
@section('title','403 Forbidden')


@section('content')
	<section class="error-section">
		<div class="container">
			<div class="row g-5 align-items-center">
				<div class="col-sm-6">
					<div class="error-thum">
						<img src="{{ asset('assets/global/img/error.png')}}" alt="...">
					</div>
				</div>
				<div class="col-sm-6">
					<div class="error-content">
						<div class="error-title">@lang('Forbidden')</div>
						<div class="error-info">
                            @yield('message')
                        </div>
						<div class="btn-area">
							<a href="{{url('/')}}" class="cmn-btn">@lang('Back To Home')</a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>
@endsection
