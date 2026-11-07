
<section class="about-section pt-100 pb-100">
    <div class="container">
        <div class="row justify-content-between">
            <div class="col-lg-5">
                <div class="section__title sticky">
                    <span class="section__cate">@lang(@$about['single']['heading'])</span>
                    <h3 class="section__title">@lang(@$about['single']['sub_heading'])</h3>
                    <p>
                        {!! __(@$about['single']['description']) !!}
                    </p>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="section__title about-content">
                    <div class="row">

                        @if(isset($about['multiple']))
                            @forelse($about['multiple'] as $item)
                                <div class="col-md-12 mb-5">
                                    <span class="section__cate">@lang(@$item['heading'])</span>
                                    <p>{!! __(@$item['description']) !!}</p>
                                </div>
                            @empty
                            @endforelse
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
