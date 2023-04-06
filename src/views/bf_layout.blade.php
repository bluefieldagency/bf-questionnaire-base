@section('header')
    <header id="sticky-nav-bar">
        <div class="header-vertical dark-mode">
            <div class="content-center">
                <div class="header-horizontal">
                    @if (isset($questionnaire))
                        <a href="{{ route('start-again') }}" class="company-logo--container"><img class="company-logo company-logo--page" src="{{ asset($questionnaire->company_logo) }}" alt="{{ $questionnaire->company_name }}"></a>

                        @if ($questionnaire->hasOption('intermediate_store_allowed') && $questionnaire->getOption('intermediate_store_allowed') === true && isset($page))
                            @if ($questionnaire->hasProgressPages() && $questionnaire->showProgressForThisPage($page))
                                <a class="large-link intermediate-store-link" href="{{ route('questionnaire.intermediate-store', ['questionnaire' => $questionnaire, 'page' => $page]) }}" target="_blank">@lang('bf::translations.intermediate-store')</a>
                                <span class="large-link large-link-separator">/</span>
                            @endif
                        @endif

                        @if ( ! $questionnaire->hasOption('contact_form_enabled') || ($questionnaire->hasOption('contact_form_enabled') && $questionnaire->getOption('contact_form_enabled') !== false))
                            <a class="large-link contact-link" href="{{ route('contact') }}" target="_blank">@lang('bf::translations.contact-us')</a>
                        @endif

                        @if ($questionnaireLogo)
                            <div class="mb-6 lg:mb-9">
                                <img src="{{ asset('storage/' . $questionnaireLogo) }}" alt="Logo" class="tenant-questionnaire-logo header-tenant-logo">
                            </div>
                        @endif
                    @endif
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

    {{-- CSRF Token --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

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

        <div id="notification_container"></div>

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
