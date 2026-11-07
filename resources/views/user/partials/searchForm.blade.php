<div class="dropdown ms-2">
    <div class="d-none d-lg-block">
        <div class="input-group input-group-merge input-group-borderless input-group-hover-light navbar-input-group">
            <div class="input-group-prepend input-group-text">
                <i class="bi-search"></i>
            </div>

            <input type="search" class="js-form-search form-control global-search"
                   placeholder="@lang("Search for a menu")"
                   aria-label="@lang("Search for a menu")" data-hs-form-search-options='{
                               "clearIcon": "#clearSearchResultsIcon",
                               "dropMenuElement": "#searchDropdownMenu",
                               "dropMenuOffset": 20,
                               "toggleIconOnFocus": true,
                               "activeClass": "focus"
                             }'>
            <a class="input-group-append input-group-text" href="javascript:void(0)">
                <i id="clearSearchResultsIcon" class="bi-x-lg d-none"></i>
            </a>
        </div>
    </div>

    <button
        class="js-form-search js-form-search-mobile-toggle btn btn-ghost-secondary btn-icon rounded-circle d-lg-none"
        type="button" data-hs-form-search-options='{
                       "clearIcon": "#clearSearchResultsIcon",
                       "dropMenuElement": "#searchDropdownMenu",
                       "dropMenuOffset": 20,
                       "toggleIconOnFocus": true,
                       "activeClass": "focus"
                     }'>
        <i class="bi-search"></i>
    </button>
    <!-- End Input Group -->

    <!-- Card Search Content -->
    <div id="searchDropdownMenu"
         class="hs-form-search-menu-content dropdown-menu dropdown-menu-form-search navbar-dropdown-menu-borderless">
        <div class="card">
            <!-- Body -->
            <div class="card-body-height search-result">
                <div class="d-lg-none">
                    <div class="input-group input-group-merge navbar-input-group mb-5">
                        <div class="input-group-prepend input-group-text">
                            <i class="bi-search"></i>
                        </div>

                        <input type="search" class="form-control global-search" placeholder="@lang("Search for a menu")">
                        <a class="input-group-append input-group-text" href="javascript:void(0);">
                            <i class="bi-x-lg"></i>
                        </a>
                    </div>
                </div>

                <span class="dropdown-header">@lang("Result")</span>
                <div class="dropdown-divider"></div>

                <div class="content">

                </div>

            </div>
        </div>
    </div>
</div>

@push('js-lib')
    <script src="{{ asset('assets/global/js/hs-form-search.min.js') }}"></script>
    <script src="{{ asset('assets/global/js/hs-form-search-custom.js') }}"></script>
@endpush


