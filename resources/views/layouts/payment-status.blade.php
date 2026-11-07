<!DOCTYPE html>
<html lang="en" @if(session()->get('rtl') == 1) dir="rtl" @endif>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Payment Status')</title>

    <link href="{{ asset('assets/themes/nova/css/bootstrap.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/admin/css/bootstrap-icons.css') }}" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <link rel="shortcut icon" href="{{ getFile(basicControl()->favicon_driver, basicControl()->favicon) }}" type="image/x-icon">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(to right, #f5f7fa, #c3cfe2);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .status-card {
            background-color: #ffffff;
            padding: 40px 30px;
            border-radius: 20px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.08);
            text-align: center;
            max-width: 700px;
            min-height: 450px;
            width: 100%;
            margin: auto;
            font-size: 1.1rem;
        }
        .status-icon {
            font-size: 64px;
            margin-top: 5px;
            margin-bottom: 15px;
        }
        .status-title {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 12px;
        }
        .status-message {
            font-size: 17px;
            color: #666;
        }
        .btn-home {
            margin-top: 25px;
            padding: 10px 25px;
            font-weight: 500;
            border-radius: 8px;
        }
        .footer{
            margin-top: 40px;
            margin-bottom: 0;
            font-size: 16px;
        }

        @media (max-width: 576px) {
            .status-card {
                padding: 30px 20px !important;
                margin: 30px 20px !important;
                font-size: 1rem !important;
            }
            .status-icon {
                font-size: 60px;
                margin-bottom: 12px;
            }
        }
    </style>

    @stack('style')

</head>
<body>

<div class="status-card">
    @yield('content')

    <p class="footer">
        @lang('Copyright') @lang('©') {{ date('Y') }}
        <a href="{{ url('/') }}" class="text-decoration-none text-success">{{ basicControl()->site_title }}</a>
        | @lang('All Rights Reserved')
    </p>
</div>

<x-notify />

</body>
</html>
