
<footer class="main-footer footer-style-1">
    <div class="container">
        <div class="row">
            <div class="col-xl-3 col-lg-4 col-md-6">
                <div class="footer-widget footer-widget-1">
                    <div class="logo-widget-inner">
                        <div class="footer-logo">
                            <a href="{{ url('/') }}" class="">
                                <img src="{{getFile(basicControl()->logo_driver, basicControl()->logo)}}"
                                     alt="@lang(basicControl()->site_title)">
                            </a>
                        </div>
                        <p>{{ __(@$footer['single']['description']) }}</p>
                        <div class="footer-media">
                            <ul>
                                @forelse(@$footer['multiple'] ?? [] as $item)
                                    <li>
                                        <a href="{{ @$item['media']->my_link }}" target="_blank">
                                            <i class="{{ @$item['media']->icon }}"></i>
                                        </a>
                                    </li>
                                @empty
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-2 offset-xl-1 col-lg-3 col-md-3">
                <div class="footer-widget footer-widget-2">
                    <h6>@lang('Useful Links')</h6>
                    <ul class="footer-widget-list">
                        @foreach(getFooterMenuData('useful_link') ?? [] as $list)
                            {!! $list !!}
                        @endforeach
                    </ul>
                </div>
            </div>

            <div class="col-xl-3 col-lg-3 col-md-3">
                <div class="footer-widget footer-widget-3">
                    <h6>@lang('Support Links')</h6>
                    <ul class="footer-widget-list">
                        @foreach(getFooterMenuData('support_link') ?? [] as $list)
                            {!! $list !!}
                        @endforeach
                    </ul>
                </div>
            </div>

            <div class="col-lg-3 col-md-4">
                <div class="footer-widget footer-widget-4">
                    <h6>@lang('Contact Us')</h6>
                    <div class="widget-4-content">
                        <ul class="footer-location">
                            <li><a href="tel:+1645364879"><i class="fa-light fa-phone"></i>
                                    {{ @$footer['single']['phone'] ?? '+155 1256322' }}</a></li>
                            <li><a href="mailto:paysecure@email.com"><i class="fa-light fa-envelope"></i>
                                    {{ @$footer['single']['email'] ?? 'support@paysecure.net' }}</a></li>
                        </ul>
                    </div>
                    <h6 class="mt_15">@lang('Address')</h6>
                    <div class="widget-4-content">
                        <ul>
                            <li><i class="fa-light fa-location-dot"></i>
                                {{ @$footer['single']['address'] ?? 'NewYork, USA.' }}
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="footer-copyright">
            <p class="copyright-text">
                @lang('Copyright') {{date('Y')}}
                <a href="javascript:void(0)">{{ __(basicControl()->site_title) }}.</a>
                @lang('All rights reserved')
            </p>
            <div class="copyright-language d-flex align-items-center gap-2">
                @foreach($languages ?? [] as $item)
                    <a href="{{ route('language', $item->short_name) }}"
                       class="{{ session('lang') == $item->short_name ? 'active' : '' }}">
                        <span class="flag-icon flag-icon-{{ strtolower($item->short_name) }}"></span>
                        @lang($item->name)
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</footer>

@if(basicControl()->cookie_status == 1 )
    <script>
        function setCookie(name, value, days) {
            var expires = "";
            if (days) {
                var date = new Date();
                date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
                expires = "; expires=" + date.toUTCString();
            }
            document.cookie = name + "=" + value + expires + "; path=/";
        }
        function hasAcceptedCookiePolicy() {
            return document.cookie.indexOf("cookie_policy_accepted=true") !== -1;
        }
        function hasClosedCookiePolicy() {
            return document.cookie.indexOf("cookie_policy_rejected=true") !== -1;
        }
        function acceptCookiePolicy() {
            setCookie("cookie_policy_accepted", "true", 365);
            document.getElementById("cookiesAlert").style.display = "none";
        }
        function closeCookieBanner() {
            setCookie("cookie_policy_rejected", "true", 365);
            document.getElementById("cookiesAlert").style.display = "none";
        }
        document.addEventListener('DOMContentLoaded', function () {
            if (!hasAcceptedCookiePolicy() && !hasClosedCookiePolicy()) {
                document.getElementById("cookiesAlert").style.display = "block";
            }
        });
    </script>


    <div class="cookies-alert" id="cookiesAlert">
        <h4 class="mt-2">@lang(@$footer['single']['cookie_title'])</h4>
        <span class="d-block mt-2"> @lang(@$footer['single']['cookie_sub_title'])
            <a href="{{ @$footer['single']['media']->cookie_button_link }}" class="link">@lang('see more')</a>
        </span>
        <a href="javascript:void(0);" class="me-2 btn-1 justify-content-center" type="button"
           onclick="acceptCookiePolicy()">@lang('Accept') <span></span></a>
        <a href="javascript:void(0);" class=" btn-1" type="button" onclick="closeCookieBanner()">@lang('Close') <span></span></a>
    </div>
@endif



