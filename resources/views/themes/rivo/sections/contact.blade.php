


<section class="contact-section pt-100 pb-100">
    <div class="container">
        <div class="contact-wrapper">
            <div class="contact-wrapper-left">

                <div>
                    <h3 class="title">@lang(@$contact['single']['heading'])</h3>
                    <p>
                        @lang(@$contact['single']['sub_heading'])
                    </p>
                </div>

                <ul class="contact-lists">

                    @if(isset($contact['multiple']))
                        @foreach($contact['multiple'] as $item)
                            <li class="contact-list-item py-3">
									<span class="icon">
										<img src="{{ getFile($item['media']->image->driver,$item['media']->image->path) }}">
									</span>
                                <span class="txt">
                                    {!! __(@$item['description']) !!}
                                </span>
                            </li>
                        @endforeach
                    @endif
                </ul>
            </div>

            <div class="contact-wrapper-right">
                <form method="post" action="{{ route('contact') }}">
                    @csrf
                    <div class="row gy-3 gy-md-4">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="name" class="form-label contact-label">@lang('Your Name')</label>
                                <input type="text"
                                       class="form-control contact-control @error('name') is-invalid @enderror"
                                       id="name" name="name" value="{{old('name')}}">

                                @error('name')
                                <span class="invalid-feedback" role="alert">@lang($message)</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="email" class="form-label contact-label">@lang('Your Email')</label>
                                <input type="text"
                                       class="form-control contact-control @error('email') is-invalid @enderror"
                                       name="email" id="email" value="{{old('email')}}">

                                @error('email')
                                <span class="invalid-feedback" role="alert">@lang($message)</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label for="subject" class="form-label contact-label">@lang('Your Subject')</label>
                                <input type="text"
                                       class="form-control contact-control @error('subject') is-invalid @enderror"
                                       name="subject" id="subject" value="{{old('subject')}}">

                                @error('subject')
                                <span class="invalid-feedback" role="alert">@lang($message)</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label for="message" class="form-label contact-label">@lang('Your Message')</label>
                                <textarea name="message" id="message"
                                          class="form-control contact-control @error('message') is-invalid @enderror">{{old('message')}}
										</textarea>

                                @error('message')
                                <span class="invalid-feedback" role="alert">@lang($message)</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="form-group">
                                <button class="cmn--btn" type="submit">@lang('Send Message')</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
