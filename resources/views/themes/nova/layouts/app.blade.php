<!DOCTYPE html>
<html lang="en" @if(session()->get('rtl') == 1) dir="rtl" @endif >

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <meta content="{{ isset($pageSeo['meta_description']) ? $pageSeo['meta_description'] : '' }}" name="description">
    <meta
        content="{{ is_array(@$pageSeo['meta_keywords']) ? implode(', ', @$pageSeo['meta_keywords']) : @$pageSeo['meta_keywords'] }}"
        name="keywords">
    <meta name="theme-color" content="{{ basicControl()->primary_color }}">
    <meta name="author" content="{{basicControl()->site_title}}">
    <meta name="robots" content="{{ isset($pageSeo['meta_robots']) ? $pageSeo['meta_robots'] : '' }}">

    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ isset(basicControl()->site_title) ? basicControl()->site_title : '' }}">
    <meta property="og:title" content="{{ isset($pageSeo['meta_title']) ? $pageSeo['meta_title'] : '' }}">
    <meta property="og:description" content="{{ isset($pageSeo['og_description']) ? $pageSeo['og_description'] : '' }}">
    <meta property="og:image" content="{{ getFile(@$pageSeo['meta_image_driver'], @$pageSeo['meta_image']) }}">

    <meta name="twitter:card" content="{{ isset($pageSeo['meta_title']) ? $pageSeo['meta_title'] : '' }}">
    <meta name="twitter:title" content="{{ isset($pageSeo['meta_title']) ? $pageSeo['meta_title'] : '' }}">
    <meta name="twitter:description"
          content="{{ isset($pageSeo['meta_description']) ? $pageSeo['meta_description'] : '' }}">
    <meta name="twitter:image" content="{{ getFile(@$pageSeo['meta_image_driver'], @$pageSeo['meta_image']) }}">

    <title> {{basicControl()->site_title}} @if(isset($pageSeo['page_title']))
            | {{str_replace(basicControl()->site_title, ' ',$pageSeo['page_title'])}}
        @else
            | @yield('title')
        @endif
    </title>

    <!-- Favicons -->
    <link href="{{ getFile(basicControl()->favicon_driver, basicControl()->favicon) }}" rel="icon">

    <link href="{{ asset(template(true) . 'css/bootstrap.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset(template(true) . 'css/style.css') }}" rel="stylesheet" type="text/css">

    <!-- Google Fonts -->
    <link
        href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i"
        rel="stylesheet">

    @include(template().'layouts.styles')
    @stack('style')

</head>

<body class="home">

<div class="page-wrapper">
     
    <!-- preloader -->
    <div class="loader-wrap">
        <div class="preloader">
            <div class="preloader-close">x</div>
            <div id="handle-preloader" class="handle-preloader">
                <div class="animation-preloader">
                    <div class="preloader-image">
                        <img src="{{ asset(template(true).'images/preloader.gif') }}" alt="loader">
                    </div>
                    <div class="txt-loading">
                        <?php
                        $words = explode(' ', basicControl()->site_title ?? 'PAYSECURE');
                        foreach ($words as $wordIndex => $word) {
                        foreach (str_split($word) as $key => $value) { ?>
                        <span data-text-preloader="<?php echo $value ?>" class="letters-loading">
                                    <?php echo $value; ?>
                                </span>
                        <?php }
                            if ($wordIndex < count($words) - 1) {
                                echo '<span class="letters-loading">&nbsp;</span>';
                            }
                        } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- preloader end -->

    @include(template().'partials.header')

    @if(request()->url() !== url('/') )
        @include(template().'partials.banner')
    @endif

    @yield('content')

    @include(template().'sections.footer')
    
    
    <reloadly-widget data-widget-id="bQ6kDmADxyf4TAQ6Guc3WdPrhTO5mY7vCunRDMIwgys"></reloadly-widget>
    <script src="https://cdn.reloadly.com/widget/v2/reloadly-widget.js"></script>

</div>

<!-- Scroll to top end -->
<div class="prgoress_indicator">
    <svg class="progress-circle svg-content" width="100%" height="100%" viewBox="-1 -1 102 102">
        <path d="M50,1 a49,49 0 0,1 0,98 a49,49 0 0,1 0,-98"/>
    </svg>
    <i class="fa-duotone fa-arrow-up"></i>
</div>
<!-- Scroll to top end -->


<!-- JS -->
@include(template().'layouts.scripts')


@include('plugins')


<x-notify/>

</body>
</html>


