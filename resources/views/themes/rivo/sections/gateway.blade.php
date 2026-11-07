

<section class="partner-section pt-50 pb-50">
    <div class="container">
        <div class="partner-slider owl-theme owl-carousel">
            @foreach($gateway['multiple'] ?? [] as $gateway)
                <div class="partner-item">
                    <img src="{{ $gateway }}" alt="..">
                </div>
            @endforeach
        </div>
    </div>
</section>
