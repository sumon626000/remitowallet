
{{--tomselect & flatpickr always true--}}

@push('css-lib')
    @if ($tomselect ?? true)
        <link rel="stylesheet" href="{{ asset('assets/admin/css/tom-select.bootstrap5.css') }}">
    @endif

    @if ($flatpickr ?? true)
        <link rel="stylesheet" href="{{ asset('assets/admin/css/flatpickr.min.css') }}">
    @endif

    @if($daterange ?? false)
        <link rel="stylesheet" type="text/css" href="{{ asset('assets/global/css/daterangepicker.css') }}"/>
    @endif

@endpush


@push('js-lib')
    @if ($tomselect ?? true)
        <script src="{{ asset('assets/admin/js/tom-select.complete.min.js') }}"></script>
    @endif

    @if ($flatpickr ?? true)
        <script src="{{ asset('assets/admin/js/flatpickr.min.js') }}"></script>
    @endif

    @if ($datatable ?? false)
        <script src="{{ asset('assets/admin/js/jquery.dataTables.min.js') }}"></script>
    @endif

    @if ($select ?? false)
        <script src="{{ asset('assets/admin/js/select.min.js') }}"></script>
    @endif

    @if ($fileattach ?? false)
        <script src="{{ asset('assets/admin/js/hs-file-attach.min.js') }}"></script>
    @endif

    @if ($clipboard ?? false)
        <script src="{{ asset('assets/admin/js/clipboard.min.js') }}"></script>
    @endif

    @if ($dropzone ?? false)
        <script src="{{ asset('assets/admin/js/dropzone.min.js') }}"></script>
    @endif

    @if ($counter ?? false)
        <script src="{{ asset('assets/admin/js/appear.min.js') }}"></script>
        <script src="{{ asset("assets/admin/js/hs-counter.min.js") }}"></script>
    @endif

    @if($daterange ?? false)
        <!---daterange always load at last-->
        <script type="text/javascript" src="{{ asset('assets/global/js/daterangepicker.min.js') }}" ></script>
    @endif

@endpush

@push('script')

    @if($datatable ?? false)
        <script>
            const noDataImg = "{{ asset('assets/admin/img/oc-error.svg') }}";
            const noDataImgDark = "{{ asset('assets/admin/img/oc-error-light.svg') }}";

            function getNoDataHtml() {
                return `
                <div class="text-center p-4">
                    <img class="dataTables-image mb-3" src="${noDataImg}" alt="No Data" data-hs-theme-appearance="default">
                    <img class="dataTables-image mb-3" src="${noDataImgDark}" alt="No Data" data-hs-theme-appearance="dark">
                    <p class="mb-0">No data to show</p>
                </div> `;
            }

            function initNormalDataTable(tableId = '.js-datatable') {
                HSCore.components.HSDatatables.init(tableId);
                let table = $(tableId).DataTable();

                table.on('draw', function () {
                    if (table.rows({ search: 'applied' }).data().length === 0) {
                        $('.dataTables_empty').html(getNoDataHtml());
                    }
                });
            }

            function initDataTable(ajaxUrl, columns, tableId = '#datatable') {
                if (!ajaxUrl || !columns.length) return;

                let formattedColumns = columns.map(column => ({
                    data: column,
                    name: column
                }));

                let hasSelect = columns.includes('checkbox') || '';

                let datatableOptions = {
                    processing: true,
                    serverSide: true,
                    ordering: false,
                    ajax: { url: ajaxUrl },
                    columns: formattedColumns,
                    language: {
                        zeroRecords: getNoDataHtml(),
                        processing: `<div><div></div><div></div><div></div><div></div></div>`
                    }
                };

                if (hasSelect) {
                    datatableOptions.select = {
                        style: 'multi',
                        selector: 'td:first-child input[type="checkbox"]',
                        classMap: {
                            checkAll: '#datatableCheckAll',
                            counter: '#datatableCounter',
                            counterInfo: '#datatableCounterInfo'
                        }
                    };
                }

                HSCore.components.HSDatatables.init($(tableId), datatableOptions);
            }

            function applyFilter(route, filters, tableIndex=0,) {
                const datatable = HSCore.components.HSDatatables.getItem(tableIndex);
                if (!datatable) return;

                let queryString = Object.keys(filters)
                    .map(key => `${encodeURIComponent(key)}=${encodeURIComponent(filters[key])}`)
                    .join('&');

                datatable.ajax.url(`${route}?${queryString}`).load();
            }

            $.fn.dataTable.ext.errMode = 'throw';

        </script>
    @endif

    <script>
        'use strict';
        $(document).on('ready', function () {

            @if ($flatpickr ?? true)
            HSCore.components.HSFlatpickr.init('.js-flatpickr');
            @endif

            @if ($tomselect ?? true)
            HSCore.components.HSTomSelect.init('.js-select');
            @endif

            @if ($fileattach ?? false)
            new HSFileAttach('.js-file-attach')
            @endif

            @if ($clipboard ?? false)
            HSCore.components.HSClipboard.init('.js-clipboard')
            @endif

            @if ($dropzone ?? false)
            HSCore.components.HSDropzone.init('.js-dropzone')
            @endif

            @if ($counter ?? false)
            new HSCounter('.js-counter')
            @endif

            @if ($daterange ?? false)
            HSCore.components.HSDaterangepicker.init('.js-daterangepicker')
            @endif

        });
    </script>

@endpush
