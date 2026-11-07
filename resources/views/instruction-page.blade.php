@extends(template().'layouts.error')
@section('title','Instruction Page')
@section('content')
    <section class="error-section">
        <div class="container">
            <div class="row g-5 align-items-center justify-content-center">

                @if(auth()->guard('admin')->check() == false)
                    <div class="col-sm-6">
                        <div class="error-thum">
                            <img src="{{ asset('assets/admin/img/error2.png')}}"  class="w-100" alt="...">
                        </div>
                    </div>
                @endif
                <div class="col-sm-6">
                    <div class="error-content">

                        <div class="error-info font-30">
                            @lang('Coming Soon Content in') `{{config('languages.langCode')[app()->currentLocale()] }}`
                        </div>
                        <p class="mt-3">
                            @lang('If there is no content available in') <span class="text-gradient">`{{config('languages.langCode')[app()->currentLocale()] }}`</span>, @lang('our administrators are working diligently to set up relevant content for our')
                            <span
                                class="text-gradient">`{{config('languages.langCode')[app()->currentLocale()] }}`</span> @lang('audience. We appreciate your patience as we strive to provide valuable information in your preferred language.')
                        </p>

                        @if(auth()->guard('admin')->check())
                            <div class="btn-area">
                                <a href="{{ route('admin.page.index', basicControl()->theme) }}"
                                   class="cmn-btn">@lang('Go To Settings')</a>
                            </div>
                        @endif
                    </div>
                </div>


                @if(auth()->guard('admin')->check())
                    <div class="col-sm-12">
                        <div class="instruction-thumbs">
                            <img src="{{ asset('assets/admin/img/content-add-instruction.png')}}" class="w-100" alt="...">
                        </div>
                    </div>
                @endif


            </div>
        </div>
    </section>
@endsection
