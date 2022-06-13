@section('header')
    <header>
        <div class="header-vertical dark-mode">
            <div class="content-center">
                <div class="header-horizontal">
                    <img class="company-logo company-logo--page" src="{{ asset($questionnaire->company_logo) }}" alt="{{ $questionnaire->company_name }}">
                    <a class="large-link contact-link" href="https://www.bluefield.eu/en/contact/" target="_blank">Neem contact op</a>
                </div>
            </div>
        </div>
    </header>
@endsection

<!doctype html>
<html lang="en-US">

<head>
    <meta charset="utf-8">
    <title>Blue Field Questionnaire</title>
    <meta name="description" content="Blue Field Questionnaire">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta property="og:title" content="Blue Field Questionnaire">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="">

    <script src="{{ mix('/js/app.js') }}"></script>

    <link rel="stylesheet" href="{{ mix('/css/app.css') }}">

    <meta name="theme-color" content="#fafafa">
</head>

<body>

    <div class="wrapper">
        @yield('header')

        <div class="wrapper-content">
            @yield('content')
        </div>

        <footer>
            <div class="footer-vertical dark-mode">
                <div class="content-center">
                    <div class="footer-horizontal">
                        <img class="company-logo company-logo--page" src="{{ asset($questionnaire->company_logo) }}" alt="{{ $questionnaire->company_name }}">
                        <span class="copyright aeonik14">&copy; {{ date('Y') }} Blue Field Agency</span>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    @stack('javascript')

</body>

</html>