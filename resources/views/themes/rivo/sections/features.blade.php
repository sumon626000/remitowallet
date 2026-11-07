

<section class="why-choose-us pt-100 pb-50">
    <div class="container">
        <div class="section__title section__title-center">
            <span class="section__cate">@lang(@$features['single']['heading'])</span>
            <h3 class="section__title">@lang(@$features['single']['sub_heading'])</h3>
            <p>
                {!! __(@$features['single']['description']) !!}
            </p>
        </div>

        <div class="app-services-block">
            <div class="app-services-inner">
                @forelse($features['multiple'] ?? [] as $key=>$item)
                    @if($key%2==0)
                        <div class="app-services-point">
                            <img src="{{ @getFile($item['media']->image->driver,$item['media']->image->path) }}" alt="..">
                            <h5 class="title">@lang(@$item['title'])</h5>
                            <p>{!!  __(@$item['sub_title'])  !!}</p>
                        </div>
                    @endif
                @empty
                @endforelse
            </div>
            <div class="app-services-inner middle-img">
                <img src="{{ @getFile($features['single']['media']->image->driver,$features['single']['media']->image->path) }}" alt="...">
            </div>
            <div class="app-services-inner">
                @forelse($features['multiple'] ?? [] as $key=>$item)
                    @if($key%2!==0)
                        <div class="app-services-point">
                            <img src="{{ @getFile($item['media']->image->driver,$item['media']->image->path) }}" alt="..">
                            <h5 class="title">@lang(@$item['title'])</h5>
                            <p>{!!  __(@$item['sub_title'])  !!}</p>
                        </div>
                    @endif
                @empty
                @endforelse
            </div>
        </div>
    </div>
</section>
