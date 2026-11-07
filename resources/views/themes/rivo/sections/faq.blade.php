

<section class="faqs-section pt-100 pb-100">
    <div class="container">
        <div class="section__title section__title-center">
            <span class="section__cate">@lang(@$faq['single']['heading'])</span>
            <h3 class="section__title">@lang(@$faq['single']['sub_heading'])</h3>
            <p>
                {!! __(@$faq['single']['description']) !!}
            </p>
        </div>
        <div class="faq-block">
            @if(isset($faq['multiple']))
                @foreach($faq['multiple'] ?? [] as $item)
                    <div class="faq-block-item {{ $loop->first ? 'active open' : '' }}">
                        <div class="faq-block-title">
                            <h5 class="title">
                                <i class="las la-question"></i>
                                @lang(@$item['question'])
                            </h5>
                        </div>
                        <div class="faq-block-content">
                            <p>
                                {!! __(@$item['answer']) !!}
                            </p>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
</section>
