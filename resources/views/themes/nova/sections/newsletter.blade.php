

<section class="newsletter">
    <div class="bg-layer" style="background: url('{{ asset(template(true).'images/background/newsletter-bg.jpg') }}');"></div>

    <div class="container">
        <div class="newsletter-container">
            <div class="newsletter-content">
                <div class="common-title">
                    <h3>@lang(@$newsletter['single']['heading'])</h3>
                    <p>@lang(@$newsletter['single']['sub_heading'])</p>
                </div>
                <div class="newsletter-form">
                    <form action="{{ route('subscribe') }}"  method="post">
                        @csrf
                        <input type="email" name="email" value="{{old('email')}}" placeholder="@lang('Your email')" required>
                        <button type="submit">@lang(@$newsletter['single']['button_name'])
                            <i class="fa-light fa-arrow-right-long"></i></button>
                    </form>
                    @error('email')<span class="text-danger">{{$message}}</span>@enderror
                </div>
            </div>
        </div>
    </div>
</section>
