
<section class="solution">
    <div class="bg-layer" style="background: url('{{ asset(template(true).'images/background/solution-bg.jpg') }}');"></div>

    <div class="container">
        <div class="row">
            <div class="col-lg-6">
                <div class="solution-left-container">
                    <div class="common-title">
                        <h6>@lang(@$features['single']['heading'])</h6>
                        <h3>@lang(@$features['single']['sub_heading'])</h3>
                        <p>
                            {!! __(@$features['single']['description']) !!}
                        </p>
                    </div>
                    <div class="solution-list">
                        <ul>
                            @forelse(@$features['multiple'] ?? [] as $key=>$item)
                                <li><i class="fa-solid fa-circle-check"></i> @lang($item['title'])</li>
                            @empty
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="solution-image">
                    <img src="{{ @$features['mediaFile']->image }}" alt="image">
                </div>
            </div>
        </div>
    </div>
</section>
