
<!-- Footer -->
<footer class="bg--title">
    <div class="container">
        <div class="pt-100 pb-100">
            <div class="footer-link-wrap">

                <div class="link-item">
                    <div class="footer-description">
                        <div class="logo">
                            <a href="{{ url('/') }}">
                                <img src="{{ getFile(basicControl()->footer_logo_driver, basicControl()->footer_logo) }}"
                                     alt="@lang(basicControl()->site_title)">
                            </a>
                        </div>
                        <p class="pt-4">
                            {{ __(@$footer['single']['description']) }}
                        </p>
                        <div class="social-icon">
                            @forelse(@$footer['multiple'] ?? [] as $item)
                                <a href="{{ @$item['media']->my_link }}" target="_blank" class="icon">
                                    <i class="{{ @$item['media']->icon }}"></i>
                                </a>
                            @empty
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="link-item">
                    <h4 class="title">@lang('Useful Links')</h4>
                    <ul>
                        @foreach(getFooterMenuData('useful_link') ?? [] as $list)
                            {!! $list !!}
                        @endforeach
                    </ul>
                </div>

                <div class="link-item">
                    <h4 class="title">@lang('Support Links')</h4>
                    <ul>
                        @foreach(getFooterMenuData('support_link') ?? [] as $list)
                            {!! $list !!}
                        @endforeach
                    </ul>
                </div>

                <div class="link-item footer-contact-info">
                    <h4 class="title">@lang('Contact Us')</h4>
                    <ul>
                        <li>
                            <div class="d-flex flex-row align-items-center gap-2 mb-4">
                                <i class="fas fa-phone-alt"></i>
                                <p class="contant-details">{{ @$footer['single']['phone'] ?? '+155 1256322' }}</p>
                            </div>
                        </li>
                        <li>
                            <div class="d-flex flex-row align-items-center gap-2 mb-4">
                                <i class="fas fa-envelope"></i>
                                <p class="contant-details">{{ @$footer['single']['email'] ?? 'support@paysecure.net' }}</p>
                            </div>
                        </li>
                        <li>
                            <div class="d-flex flex-row align-items-center gap-2 mb-3">
                                <i class="fas fa-address-book"></i>
                                <p class="contant-details">{{ @$footer['single']['address'] ?? 'NewYork, USA.' }}</p>
                            </div>
                        </li>

                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="border-top py-3 text--light border--top">
        <div class="container text-center">
            @lang('Copyright') {{date('Y')}}
            <a href="javascript:void(0)" class="text--base">{{ __(basicControl()->site_title) }}</a>
            @lang('All rights reserved')
        </div>
    </div>
</footer>
<!-- Footer -->



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
        function acceptCookiePolicy() {
            setCookie("cookie_policy_accepted", "true", 365);
            document.getElementById("cookiesAlert").style.display = "none";
        }
        function closeCookieBanner() {
            document.getElementById("cookiesAlert").style.display = "none";
        }
        document.addEventListener('DOMContentLoaded', function () {
            if (!hasAcceptedCookiePolicy()) {
                document.getElementById("cookiesAlert").style.display = "block";
            }
        });
    </script>

    <div class="cookie-content d-none" id="cookiesAlert">
        <div class="content">
            <h5 class="title">@lang(@$footer['single']['cookie_title'])</h5>
            <p>
                @lang(@$footer['single']['cookie_sub_title'])
                <a href="{{ @$footer['single']['media']->cookie_button_link }}" class="text--base">@lang('Read More')</a>
            </p>
            <div class="btn__grp">
                <a href="javascript:void(0)" class="cmn--btn btn-sm btn--success"
                   onclick="acceptCookiePolicy()"
                   id="cookie-accept">@lang('Accept All')</a>
                <a href="javascript:void(0)" class="cmn--btn btn-sm btn--danger"
                   onclick="closeCookieBanner()"
                   id="cookie-deny">@lang('Decline All')</a>
            </div>
        </div>
    </div>

@endif
