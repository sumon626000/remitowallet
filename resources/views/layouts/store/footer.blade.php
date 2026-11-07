<footer class="footer-section">
	<div class="container">
		<div class="row gy-5 gy-lg-0">
			<div class="col-12">
				<div class="footer-box">
					<a class="navbar-brand" href="javascript:void(0)"> <img src="{{shopImage($link)}}" alt="..."/></a>
					<div class="copyright">
						@lang('Copyright') {{date('Y')}}
                        <a href="javascript:void(0)" class="text--base">{{ __(basicControl()->site_title) }}</a>
                        @lang('All rights reserved')
					</div>
				</div>
			</div>
		</div>
	</div>
</footer>
