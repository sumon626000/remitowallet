

<section class="add-slider">
    <div class="container">
        <div class="add-container">
            <h6>@lang(@$brand['single']['heading'])</h6>
            <div class="add-content">
                <ul class="add-content-inner">
                    @forelse(@$brand['multiple'] ?? [] as $key=>$item)
                        <li><img src="{{ @getFile($item['media']->image->driver,$item['media']->image->path) }}" alt="icon"></li>
                    @empty
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</section>

