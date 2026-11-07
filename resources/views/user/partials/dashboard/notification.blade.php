


<div id="firebase-app">
    <div v-if="user_foreground == '1' || user_background == '1'"  v-cloak>
        <div class="alert alert-soft-dark mb-4 mb-lg-7 p-3" role="alert"
             v-if="notificationPermission == 'default' && !is_notification_skipped" v-cloak>
            <div class="alert-box d-flex flex-wrap align-items-center gap-2">
                <div class="flex-shrink-0">
                    <img class="avatar avatar-lg" src="{{ asset('assets/admin/img/oc-megaphone.svg') }}"
                         alt="Image Description" data-hs-theme-appearance="default">
                    <img class="avatar avatar-lg" src="{{ asset('assets/admin/img/oc-megaphone-light.svg') }}"
                         alt="Image Description" data-hs-theme-appearance="dark">
                </div>

                <div class="flex-grow-1 ms-3">
                    <h3 class="mb-1">@lang("Attention!")</h3>
                    <div class="d-flex align-items-center flex-column flex-sm-row gap-2">
                        <p class="mb-0 text-body">
                            @lang('Do not miss any single important notification! Allow your browser to get instant push notification')
                        </p>
                        <button id="allow-notification" class="btn btn-sm btn-primary mx-2">
                            <i class="bi bi-check-circle"></i> @lang('Allow me')</button>
                    </div>
                </div>
                <button type="button" class="btn-close" @click.prevent="skipNotification" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    </div>

    <div class="alert alert-soft-dark mb-4 mb-lg-7 p-3" role="alert" v-if="notificationPermission == 'denied' && !is_notification_skipped" v-cloak>
        <div class="d-flex align-items-center ">
            <div class="flex-shrink-0">
                <img class="avatar avatar-lg" src="{{ asset('assets/admin/img/oc-megaphone.svg') }}"
                     alt="Image Description" data-hs-theme-appearance="default">
                <img class="avatar avatar-lg" src="{{ asset('assets/admin/img/oc-megaphone-light.svg') }}"
                     alt="Image Description" data-hs-theme-appearance="dark">
            </div>
            <div class="flex-grow-1 ms-3">
                <h3 class="mb-1">@lang("Attention!")</h3>
                <div class="d-flex align-items-center">
                    <p class="mb-0 text-body">
                        @lang('Please allow your browser to get instant push notification. Allow it from notification setting.')
                    </p>
                </div>
            </div>
            <button type="button" class="btn-close" @click.prevent="skipNotification" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
</div>


@push('script')
    <script type="module">

        import {initializeApp} from "https://www.gstatic.com/firebasejs/9.17.1/firebase-app.js";
        import {
            getMessaging,
            getToken,
            onMessage
        } from "https://www.gstatic.com/firebasejs/9.17.1/firebase-messaging.js";

        const firebaseConfig = {
            apiKey: "{{$firebaseNotify['apiKey']}}",
            authDomain: "{{$firebaseNotify['authDomain']}}",
            projectId: "{{$firebaseNotify['projectId']}}",
            storageBucket: "{{$firebaseNotify['storageBucket']}}",
            messagingSenderId: "{{$firebaseNotify['messagingSenderId']}}",
            appId: "{{$firebaseNotify['appId']}}",
            measurementId: "{{$firebaseNotify['measurementId']}}"
        };

        const app = initializeApp(firebaseConfig);
        const messaging = getMessaging(app);
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('{{ getProjectDirectory() }}' + `/firebase-messaging-sw.js`, {scope: './'}).then(function (registration) {
                    requestPermissionAndGenerateToken(registration);
                }
            ).catch(function (error) {
            });
        } else {
        }

        onMessage(messaging, (payload) => {
            if (payload.data.foreground || parseInt(payload.data.foreground) == 1) {
                const title = payload.notification.title;
                const options = {
                    body: payload.notification.body,
                    icon: payload.notification.icon,
                };
                new Notification(title, options);
            }
        });

        function requestPermissionAndGenerateToken(registration) {
            document.addEventListener("click", function (event) {
                if (event.target.id == 'allow-notification') {
                    Notification.requestPermission().then((permission) => {
                        if (permission === 'granted') {
                            getToken(messaging, {
                                serviceWorkerRegistration: registration,
                                vapidKey: "{{$firebaseNotify['vapidKey']}}"
                            })
                                .then((token) => {
                                    $.ajax({
                                        url: "{{ userRoute('save.token') }}",
                                        method: "post",
                                        data: {
                                            token: token,
                                        },
                                        success: function (res) {
                                        }
                                    });
                                    window.newApp.notificationPermission = 'granted';
                                });
                        } else {
                            window.newApp.notificationPermission = 'denied';
                        }
                    });
                }
            });
        }
    </script>
    <script>
        window.newApp = new Vue({
            el: "#firebase-app",
            data: {
                user_foreground: '',
                user_background: '',
                notificationPermission: Notification.permission,
                is_notification_skipped: sessionStorage.getItem('is_notification_skipped') == '1'
            },
            mounted() {
                sessionStorage.clear();
                this.user_foreground = "{{$firebaseNotify['user_foreground']}}";
                this.user_background = "{{$firebaseNotify['user_background']}}";
            },
            methods: {
                skipNotification() {
                    sessionStorage.setItem('is_notification_skipped', '1')
                    this.is_notification_skipped = true;
                }
            }
        });
    </script>
@endpush
