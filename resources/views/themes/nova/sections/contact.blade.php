<section class="contact">
    <div class="container">
        <div class="row">
            <div class="col-lg-7">
                <div class="contact-container">
                    <h3>@lang(@$contact['single']['heading'])</h3>
                    <div class="contact-form">
                        <form method="post" action="{{ route('contact') }}">
                            @csrf
                            <div class="row">
                                <div class="col-lg-6">
                                    <label for="name" class="">@lang('Your Name')</label>
                                    <input type="text"
                                           class="form-control @error('name') is-invalid @enderror"
                                           id="name" name="name" value="{{old('name')}}">
                                    @error('name')
                                        <span class="invalid-feedback" role="alert">@lang($message)</span>
                                    @enderror
                                </div>
                                <div class="col-lg-6">
                                    <label for="email" class=" contact-label">@lang('Your Email')</label>
                                    <input type="text"
                                           class="form-control @error('email') is-invalid @enderror"
                                           name="email" id="email" value="{{old('email')}}">
                                    @error('email')
                                    <span class="invalid-feedback" role="alert">@lang($message)</span>
                                    @enderror
                                </div>
                            </div>

                            <label for="subject" class=" mt-2">@lang('Your Subject')</label>
                            <input type="text"
                                   class="form-control @error('subject') is-invalid @enderror"
                                   name="subject" id="subject" value="{{old('subject')}}">
                            @error('subject')
                            <span class="invalid-feedback" role="alert">@lang($message)</span>
                            @enderror

                            <label for="message" class=" mt-2">@lang('Your Message')</label>
                            <textarea name="message" id="message" rows="3"
                                  class="form-control @error('message') is-invalid @enderror">{{old('message')}}
                            </textarea>
                            @error('message')
                            <span class="invalid-feedback" role="alert">@lang($message)</span>
                            @enderror

                            <button type="submit" class="btn-1">@lang(@$contact['single']['button_name'])
                                <i class="fa-light fa-arrow-right-long"></i> <span></span></button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="contact-image">
                    <img src="{{ @$contact['mediaFile']->image  }}" alt="image">
                </div>
            </div>
        </div>
    </div>
</section>
