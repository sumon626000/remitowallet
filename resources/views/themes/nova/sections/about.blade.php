
<section class="about">
    <div class="container">
        <div class="about-container">
            <div class="row">
                <div class="col-lg-6">
                    <div class="about-left-content">
                        <div class="common-title">
                            <h6>@lang(@$about['single']['heading'])</h6>
                            <h3>@lang(@$about['single']['sub_heading'])</h3>
                            <p>
                                {!! __(@$abou['single']['description']) !!}
                            </p>
                            <a href="{{ @$about['mediaFile']->button_link }}" class="btn-1">@lang(@$about['single']['button_name'])
                                <i class="fa-light fa-arrow-right-long"></i> <span></span></a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="about-right-content">
                        <div class="about-image">
                            <img src="{{ @$about['mediaFile']->image }}" alt="image">
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="about-mission">
                        <h4>@lang(@$about['single']['mission_title'])</h4>
                        <p>{!! __(@$about['single']['mission_summary']) !!}</p>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="about-mission">
                        <h4>@lang(@$about['single']['vision_title'])</h4>
                        <p>{!! __(@$about['single']['vision_summary']) !!}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

