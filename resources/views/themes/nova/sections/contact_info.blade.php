
<section class="contact-info">
    <div class="container">
        <div class="common-title">
            <h3>@lang(@$contact_info['single']['title'])</h3>
            <p>@lang(@$contact_info['single']['sub_title'])</p>
        </div>
        <div class="contact-details-container">
            <div class="row">
                @forelse(@$contact_info['multiple'] as $item)
                    <div class="col-lg-3 col-md-6">
                        <div class="contact-details-content">
                            <div class="contact-details-icon">
                                <i class="{{ $item['media']->icon }}"></i>
                            </div>
                            <div class="contact-details-info">
                                <h6>@lang($item['name'])</h6>
                                <a href="javascript:;">@lang($item['value'])</a>
                            </div>
                        </div>
                    </div>
                @empty
                @endforelse
            </div>
        </div>
    </div>
</section>
