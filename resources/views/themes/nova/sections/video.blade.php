
<div class="video-one">
    <div class="video-one__bg" data-jarallax data-speed="0.2" data-imgPosition="50% 0%"
         style="background-image:url('{{ $video['mediaFile']->image }}') ;"
    ></div>

    <div class="video-one__box">
        <div class="missiom-video-btn">
            <a href="{{ $video['mediaFile']->my_link }}" class="video-btn video-popup">
                <i class="fas fa-play"></i>
            </a>
        </div>
    </div>
</div>
