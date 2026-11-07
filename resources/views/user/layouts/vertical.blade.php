<!DOCTYPE html>
<html lang="en" @if(session()->get('rtl') == 1) dir="rtl" @endif />
<head>
    <!-- Required Meta Tags Always Come First -->
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!-- Title -->
    <title> @yield('title') | {{basicControl()->site_title}} </title>
    <!-- Favicon -->
    <link href="{{ getFile($basicControl->favicon_driver, $basicControl->favicon) }}" rel="shortcut icon">
    <!-- Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <!-- CSS Implementing Plugins -->
    <link rel="stylesheet" href="{{ asset('assets/admin/css/bootstrap-icons.css') }}">
    <link rel="preload" href="{{ asset('assets/admin/css/theme.min.css') }}" data-hs-appearance="default" as="style">
    <link rel="preload" href="{{ asset('assets/admin/css/theme-dark.min.css') }}" data-hs-appearance="dark" as="style">
    <link rel="stylesheet" href="{{ asset('assets/global/css/select2.min.css') }}">

    <!--custom-css-->
    <link rel="preload" href="{{ asset('assets/global/css/custom-dark.css') }}" data-hs-appearance="dark" as="style">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/custom.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/user/css/dashboard.css') }}">

    @stack('css-lib')

    @stack('style')

    <style data-hs-appearance-onload-styles>
        * {
            transition: unset !important;
        }

        body {
            opacity: 0;
        }
    </style>

    <script>
        var basicControl = @json(basicControl()) ||
        '';
        var baseCurrencySymbol = @json(basicControl()->currency_symbol) ||
        '$';
        var baseCurrencyCode = @json(basicControl()->base_currency) ||
        'USD';
    </script>

    <script>
        window.hs_config = {
            "autopath": "@@autopath",
            "deleteLine": "hs-builder:delete",
            "deleteLine:build": "hs-builder:build-delete",
            "deleteLine:dist": "hs-builder:dist-delete",
            "previewMode": false,
            "startPath": "",
            "vars": {
                "themeFont": "https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap",
                "version": "?v=1.0"
            },
            "layoutBuilder": {
                "extend": {"switcherSupport": true},
                "header": {"layoutMode": "default", "containerMode": "container-fluid"},
                "sidebarLayout": "default"
            },
            "themeAppearance": {
                "layoutSkin": "default",
                "sidebarSkin": "default",
                "styles": {
                    "colors": {
                        "primary": "#377dff",
                        "transparent": "transparent",
                        "white": "#fff",
                        "dark": "132144",
                        "gray": {"100": "#f9fafc", "900": "#1e2022"}
                    }, "font": "Inter"
                }
            },
            "languageDirection": {"lang": "en"},
            "minifyCSSFiles": ["assets/css/theme.css", "assets/css/theme-dark.css", "assets/css/custom.css"],
            "copyDependencies": {
                "dist": {"*assets/js/theme-custom.js": ""},
                "build": {"*assets/js/theme-custom.js": "", "node_modules/bootstrap-icons/font/*fonts/**": "assets/css"}
            },
            "buildFolder": "",
            "replacePathsToCDN": {},

            "fileTypes": "jpg|png|svg|mp4|webm|ogv|json"
        }

    </script>
</head>

<body class="footer-offset has-navbar-vertical-aside navbar-vertical-aside-show-xl">

<script src="{{ asset('assets/admin/js/hs.theme-appearance.js') }}"></script>
<script src="{{ asset('assets/admin/js/hs-navbar-vertical-aside-mini-cache.js') }}"></script>

<!-- ========== HEADER ========== -->
@include('user.partials.navbar')

<!-- ========== SIDEBAR ========== -->
@if($view = resolveUserView('sidebar'))
    @include($view, ['user' => auth()->user()])
@endif

<main id="content" role="main" class="main">
    @yield('content')
    @stack('loadModal')
</main>


<!-- JS Global Compulsory  -->
<script src="{{ asset('assets/global/js/jquery.min.js') }}"></script>
<script src="{{ asset('assets/global/js/vue.min.js') }}"></script>
<script src="{{ asset('assets/global/js/axios.min.js') }}"></script>
<script src="{{ asset('assets/global/js/pusher.min.js') }}"></script>
<script src="{{ asset('assets/global/js/notiflix-aio-3.2.6.min.js') }}"></script>

<!-- JS Implementing Plugins -->
<script src="{{ asset('assets/admin/js/jquery-migrate.min.js') }}"></script>
<script src="{{ asset('assets/admin/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('assets/admin/js/hs-navbar-vertical-aside.min.js') }}"></script>
<script src="{{ asset('assets/admin/js/hs-form-search.min.js') }}"></script>
<script src="{{ asset('assets/admin/js/theme.min.js') }}"></script>
<script src="{{ asset('assets/admin/js/js-switch-element.min.js') }}"></script>
<script src="{{ asset('assets/admin/js/main.js') }}"></script>

<!--extra-plugins-->
<script src="{{ asset('assets/global/js/select2.min.js') }}"></script>
@stack('js-lib')
@stack('script')

@auth
    @if(basicControl()->in_app_notification == 1)
        <script>
            'use strict';
            let pushNotificationArea = new Vue({
                el: "#pushNotificationArea",
                data: {
                    items: [],
                },
                mounted() {
                    this.getNotifications();
                    this.pushNewItem();
                },
                methods: {
                    getNotifications() {
                        let app = this;
                        axios.get("{{ route('user.push.notification.show') }}")
                            .then(function (res) {
                                app.items = res.data;
                            })
                    },
                    readAt(id, link) {
                        let app = this;
                        let url = "{{ route('user.push.notification.readAt', 0) }}";
                        url = url.replace(/.$/, id);
                        axios.get(url)
                            .then(function (res) {
                                if (res.status) {
                                    app.getNotifications();
                                    if (link != '#') {
                                        window.location.href = link
                                    }
                                }
                            })
                    },
                    readAll() {
                        let app = this;
                        let url = "{{ route('user.push.notification.readAll') }}";
                        axios.get(url)
                            .then(function (res) {
                                if (res.status) {
                                    app.items = [];
                                }
                            })
                    },
                    pushNewItem() {
                        let app = this;
                        // Pusher.logToConsole = true;
                        let pusher = new Pusher("{{ env('PUSHER_APP_KEY') }}", {
                            encrypted: true,
                            cluster: "{{ env('PUSHER_APP_CLUSTER') }}"
                        });
                        let channel = pusher.subscribe('user-notification.' + "{{ Auth::id() }}");
                        channel.bind('App\\Events\\UserNotification', function (data) {
                            app.items.unshift(data.message);
                        });
                        channel.bind('App\\Events\\UpdateUserNotification', function (data) {
                            app.getNotifications();
                        });
                    }
                }
            });
        </script>
    @endif
@endauth

<script>
    (function () {
        const $dropdownBtn = document.getElementById('selectThemeDropdown') // Dropdown trigger
        const $variants = document.querySelectorAll(`[aria-labelledby="selectThemeDropdown"] [data-icon]`)

        const setActiveStyle = function () {
            $variants.forEach($item => {
                if ($item.getAttribute('data-value') === HSThemeAppearance.getOriginalAppearance()) {
                    $dropdownBtn.innerHTML = `<i class="${$item.getAttribute('data-icon')}" />`
                    return $item.classList.add('active')
                }
                $item.classList.remove('active')
            })
        }

        $variants.forEach(function ($item) {
            $item.addEventListener('click', function () {
                var $theme = $item.getAttribute('data-value');
                if ($theme == 'auto') {
                    $('aside').removeClass('navbar-bordered bg-white navbar-vertical-aside-initialized')
                    $('aside').addClass('navbar-dark bg-dark navbar-vertical-aside-initialized')
                } else if ($theme == 'default') {
                    $('aside').removeClass('navbar-dark bg-dark navbar-vertical-aside-initialized')
                    $('aside').addClass('navbar-bordered bg-white navbar-vertical-aside-initialized')
                }
                HSThemeAppearance.setAppearance($theme)

                $.ajax({
                    url: "{{route('admin.themeMode')}}/" + $theme,
                    type: 'get',
                    success: function (response) {
                        if (response != 'dark') {
                            if (response == 'auto') {
                                var $themeImgSource = "{{ getFile($basicControl->admin_dark_mode_logo_driver, $basicControl->admin_dark_mode_logo, true) }}";
                            } else {
                                var $themeImgSource = "{{ getFile($basicControl->admin_logo_driver, $basicControl->admin_logo, true) }}";
                            }
                            var element = document.querySelector('.navbar-brand-logo-auto');
                            if (element) {
                                element.setAttribute('src', $themeImgSource);
                            }
                        }
                    }
                });
            })
        })
        setActiveStyle()
        window.addEventListener('on-hs-appearance-change', function () {
            setActiveStyle()
        })
    })();
</script>

<x-notify/>

</body>
</html>
