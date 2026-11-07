
<div class="card mb-3 mb-lg-5">
    <div class="card-header card-header-content-between">
        <h4 class="card-header-title">@lang("Users Overview")</h4>
    </div>
    <div class="card-body">
        <div style="height: 21rem;" class="mb-3">
            <div class="js-jsvectormap jsvectormap-custom"
                 data-hs-js-vector-map-options='{
          "focusOn": {
            "coords": [25, 12],
            "scale": 1.5,
            "animate": true
          },
          "regionStyle": {
            "initial": {
              "fill": "rgba(55, 125, 255, .3)"
            },
            "hover": {
              "fill": "#377dff"
            }
          },
          "backgroundColor": "#132144",
          "markerStyle": {
            "initial": {
              "stroke-width": 2,
              "fill": "rgba(255,255,255,.5)",
              "stroke": "rgba(255,255,255,.5)",
              "r": 6
            },
            "hover": {
              "fill": "#fff",
              "stroke": "#fff"
            }
          }
        }'>
            </div>
        </div>
    </div>
</div>




@push('css-lib')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/jsvectormap.min.css') }}">
@endpush

@push('js-lib')
    <script src="{{ asset('assets/admin/js/jsvectormap.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/jsvectormap.js') }}"></script>
    <script src="{{ asset('assets/admin/js/world.js') }}"></script>
@endpush


@push('script')
    <script>
        fetch("{{ route('admin.user.overview') }}")
            .then(response => response.json())
            .then(data => {

                const markers = data.map(user => ({
                    coords: user.coords,
                    name: user.name,
                    active: user.active,
                    new: user.new,
                    flag: user.flag,
                    code: user.code
                }));

                const tooltipTemplate = function(marker) {
                    return `
                        <span class="d-flex align-items-center mb-2">
                          <img class="avatar avatar-xss avatar-circle" src="${marker.flag}" alt="Flag">
                          <span class="h5 ms-2 mb-0">${marker.name}</span>
                        </span>
                        <div class="d-flex justify-content-between" style="max-width: 10rem;">
                          <strong>{{ trans('Total User') }}:</strong>
                          <span class="ms-2">${marker.active}</span>
                        </div>
                      `;
                };

                HSCore.components.HSJsVectorMap.init('.js-jsvectormap', {
                    markers,
                    onRegionTooltipShow(map, tooltip, code) {
                        let marker = markers.find(function (marker) {
                            return marker.code === code;
                        });

                        if (marker) {
                            tooltip._tooltip.style.display = null;
                            tooltip._tooltip.innerHTML = tooltipTemplate(marker);
                        } else {
                            tooltip._tooltip.style.display = 'none';
                        }
                    },
                    onMarkerTooltipShow: function (map, tooltip, code) {
                        tooltip._tooltip.style.display = null;
                        tooltip._tooltip.innerHTML = tooltipTemplate(markers[code]);
                    },
                    backgroundColor: HSThemeAppearance.getAppearance() === 'dark' ? '#25282a' : '#132144'
                })

            });
    </script>

@endpush
