
<script src="{{ asset('assets/global/js/jquery.min.js') }}"></script>
<script src="{{ asset('assets/global/js/notiflix-aio-3.2.6.min.js') }}"></script>

<script src="{{ asset(template(true) . 'js/bootstrap.min.js') }}"></script>
<script src="{{ asset(template(true) . 'js/viewport.jquery.js') }}"></script>
<script src="{{ asset(template(true) . 'js/odometer.min.js') }}"></script>
<script src="{{ asset(template(true) . 'js/lightbox.min.js') }}"></script>
<script src="{{ asset(template(true) . 'js/owl.min.js') }}"></script>
<script src="{{ asset(template(true) . 'js/main.js') }}"></script>

<script src="https://www.google.com/recaptcha/api.js" async defer></script>

@stack('js-lib')


@stack('script')

@stack('extra_scripts')
