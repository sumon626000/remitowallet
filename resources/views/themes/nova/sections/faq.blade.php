

<section class="faq">
    <div class="container">
        <div class="row">
            <div class="col-lg-4">
                <div class="faq-left-container">
                    <div class="common-title">
                        <h6>FAQs</h6>
                        <h3>Looking For Answer Already ?</h3>
                    </div>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="faq-right-container">
                    <div class="faq-page-accordion">
                        <div class="accordion accordion-flush" id="accordionFlushExample">
                            @if(isset($faq['multiple']))
                                @foreach($faq['multiple'] as $key => $item)
                                    @php
                                        $collapseId = 'flush-collapse-' . $key;
                                        $isFirst = $key === 0;
                                    @endphp
                                    <div class="accordion-item">
                                        <h6 class="accordion-header">
                                            <button
                                                class="accordion-button {{ $isFirst ? '' : 'collapsed' }}"
                                                type="button"
                                                data-bs-toggle="collapse"
                                                data-bs-target="#{{ $collapseId }}"
                                                aria-expanded="{{ $isFirst ? 'true' : 'false' }}"
                                                aria-controls="{{ $collapseId }}">
                                                @lang(@$item['question'])
                                            </button>
                                        </h6>
                                        <div
                                            id="{{ $collapseId }}"
                                            class="accordion-collapse collapse {{ $isFirst ? 'show' : '' }}"
                                            data-bs-parent="#accordionFlushExample">
                                            <div class="accordion-body">
                                                <p>{!! __(@$item['answer']) !!}</p>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>
