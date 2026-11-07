<!DOCTYPE html>
<head>
    <meta charset="UTF-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <link rel="icon" type="image/x-icon" href="{{ getFile(basicControl()->favicon_driver,basicControl()->favicon) }}"/>
    <title>{{ basicControl()->site_title }}</title>

    <link rel="stylesheet" href="{{ asset(template(true) . 'css/bootstrap.min.css')}}"/>
    <link rel="stylesheet" href="{{ asset(template(true) . 'css/bootstrap.css')}}"/>
    <link rel="stylesheet" href="{{ asset(template(true) . 'css/main.css')}}"/>
    <link rel="stylesheet" href="{{ asset(template(true) . 'css/style.css')}}"/>


    <style>
        /*error section start*/
        .error-section {
            z-index: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            background-color: var(--bg-2); /* Customize the background color */
            padding: 50px 0;
            text-align: center;
        }

        .error-section .container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100%;
        }

        .error-section .error-thum img {
            max-width: 100%;
            height: auto;
            object-fit: contain;
            animation: float 3s ease-in-out infinite; /* Subtle animation */
        }

        @keyframes float {
            0% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-10px);
            }
            100% {
                transform: translateY(0);
            }
        }

        .error-section .error-content {
            color: var(--soft-red);
            padding: 30px;
            text-align: left;
        }

        .error-section .error-title {
            font-size: 120px;
            line-height: 1;
            font-weight: bold;
            color: var(--soft-red); /* Customize error color */
            margin-bottom: 20px;
        }

        .error-section .error-info {
            font-size: 24px;
            margin-bottom: 20px;
            font-weight: 500;
            color: var(--secondary-color);
        }

        .error-section .text-gradient {
            background: linear-gradient(45deg, #ff6a6a, #212023); /* Customize gradient */
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .error-section .btn-area {
            margin-top: 30px;
        }

        .error-section .btn-1 {
            background-color: var(--primary-color);
            color: #fff;
            padding: 15px 30px;
            border-radius: 50px;
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .error-section .btn-1:hover {
            background-color: var(--secondary-color);
            color: #fff;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .error-section .error-title {
                font-size: 80px;
            }

            .error-section .error-info {
                font-size: 20px;
            }

            .error-section .btn-1 {
                padding: 12px 25px;
                font-size: 14px;
            }
        }

        .media-login img {
            min-width: 36px !important;
        }

        .cmn-btn {
            background-color: #ffa500;
            text-decoration: none;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            font-size: 16px;
            transition: all 0.3s ease-in-out;
        }

        .cmn-btn:hover {
            background-color: #32cd32;
            color: #ffffff;
            border-color: #228b22;
        }

    </style>

</head>

<body class="">

@yield('content')

<script src="{{ asset(template(true) . 'js/bootstrap.bundle.min.js')}}"></script>

</body>
</html>
