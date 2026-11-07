

<section class="get-payment-section pt-100 pb-100 bg--section">
    <div class="container">
        <div class="row justify-content-between flex-wrap-reverse">
            <div class="col-lg-6">
                @forelse(@$service['multiple']  ?? [] as $item)
                    <div class="get-payment-item bg--body">
                        <div class="img">
                            <img src="{{ @getFile($item['media']->image->driver,$item['media']->image->path) }}" alt="...">
                        </div>
                        <div class="cont">
                            <h5 class="title">@lang(@$item['title'])</h5>
                            <p>
                                {!! __(@$item['sub_title']) !!}
                            </p>
                        </div>
                    </div>
                @empty
                @endforelse
            </div>
            <div class="col-lg-5">
                <div class="section__title sticky">
                    <span class="section__cate">@lang(@$service['single']['heading'])</span>
                    <h3 class="section__title">@lang(@$service['single']['sub_heading'])</h3>
                    <p>
                        {!! __(@$service['single']['description']) !!}
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>
