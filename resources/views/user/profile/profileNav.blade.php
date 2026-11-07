
<div class="col-lg-3">
    <div class="navbar-expand-lg navbar-vertical mb-3 mb-lg-5">
        <div class="d-grid">
            <button type="button" class="navbar-toggler btn btn-white mb-3" data-bs-toggle="collapse"
                    data-bs-target="#navbarVerticalNavMenu" aria-label="Toggle navigation"
                    aria-expanded="false" aria-controls="navbarVerticalNavMenu">
                                <span class="d-flex justify-content-between align-items-center">
                                  <span class="text-dark">@lang('Menu')</span>
                                  <span class="navbar-toggler-default">
                                    <i class="bi-list"></i>
                                  </span>
                                  <span class="navbar-toggler-toggled">
                                    <i class="bi-x"></i>
                                </span>
                            </span>
            </button>
        </div>

        <div id="navbarVerticalNavMenu" class="collapse navbar-collapse">
            <ul id="navbarSettings"
                class="js-sticky-block js-scrollspy card card-navbar-nav nav nav-tabs nav-lg nav-vertical"
                data-hs-sticky-block-options='{
                             "parentSelector": "#navbarVerticalNavMenu",
                             "targetSelector": "#header",
                             "breakpoint": "lg",
                             "startPoint": "#navbarVerticalNavMenu",
                             "endPoint": "#stickyBlockEndPoint",
                             "stickyOffsetTop": 20
                           }'>
                <li class="nav-item">
                    <a class="nav-link active" href="{{route('user.profile')}}">
                        <i class="bi-person nav-icon"></i> @lang('Basic information')
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#passwordSection">
                        <i class="bi-key nav-icon"></i> @lang('Password')
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#recentDevicesSection">
                        <i class="bi-phone nav-icon"></i> @lang('Recent devices')
                    </a>
                </li>

            </ul>
        </div>
    </div>
</div>
