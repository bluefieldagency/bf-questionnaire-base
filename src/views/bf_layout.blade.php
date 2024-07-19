@section('header')
    <header id="sticky-nav-bar">
        <div class="header-vertical dark-mode">
            <div class="content-center">
                <div class="header-horizontal">
                    @if (isset($questionnaire))
                        <a href="{{ route('start-again') }}" class="company-logo--container"><img class="company-logo company-logo--page" src="{{ asset($questionnaire->company_logo) }}" alt="{{ config('app.name') }}"></a>

                        @if ($questionnaire->hasOption('intermediate_store_allowed') && $questionnaire->getOption('intermediate_store_allowed') === true && isset($page))
                            @if ($questionnaire->hasProgressPages() && $questionnaire->showProgressForThisPage($page))
                                <a class="large-link intermediate-store-link" href="{{ $questionnaire->getRoute('intermediate-store', $page) }}" target="_blank">
                                    @if ($questionnaire->hasOption('intermediate_store_label'))
                                        {{ $questionnaire->getOption('intermediate_store_label') }}
                                    @else
                                        @lang('bf::translations.intermediate-store')
                                    @endif
                                </a>

                                @if ($questionnaire->hasOption('intermediate_store_separator') && $questionnaire->getOption('intermediate_store_separator') === true)
                                    <span class="large-link large-link-separator">/</span>
                                @endif
                            @endif
                        @endif

                        @if (session()->has('tenant_languages') && ($questionnaire->hasOption('is_translatable') && $questionnaire->getOption('is_translatable') !== false))
                            <nav class="language-dropdown dark-mode">
                                <div class="dropdown-button large-link grey">@svg('globe') <span>EN</span></div>

                                <ul class="dropdown-content">
                                    @foreach(session('tenant_languages') as $code => $languageName)
                                        <li><a href="{{ $questionnaire->getRoute('switch-language', $page, ['language' => $code]) }}" class="aeonik16">{{ ucfirst($languageName) }}</a></li>
                                    @endforeach
                                </ul>
                            </nav>
                        @endif

                        @if ( ! $questionnaire->hasOption('contact_form_enabled') || ($questionnaire->hasOption('contact_form_enabled') && $questionnaire->getOption('contact_form_enabled') !== false))
                            <a class="large-link contact-link" href="{{ route('contact') }}" target="_blank">@lang('bf::translations.contact-us')</a>
                        @endif

                        @if ($questionnaireLogo)
                            <div class="mb-6 lg:mb-9 questionnaire-company-logo">
                                <img src="{{ $questionnaireLogo }}" alt="Logo" class="tenant-questionnaire-logo header-tenant-logo">
                            </div>
                        @endif
                    @elseif ( ! empty(config('project.default_logo_inverted')))
                        <a href="{{ route('home') }}" class="company-logo--container"><img class="company-logo company-logo--page" src="{{ asset(config('project.default_logo_inverted')) }}" alt="{{ config('app.name') }}"></a>
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
    <title>{{ config('app.name') }}</title>
    <meta name="description" content="{{ config('app.name') }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta property="og:title" content="{{ config('app.name') }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="">

    {{-- CSRF Token --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script src="{{ mix('/js/app.js') }}"></script>

    <link rel="stylesheet" href="{{ mix('/css/app.css') }}">

    @includeIf('partials.favicons')

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
                        @if ($questionnaire)
                            <img class="company-logo company-logo--page" src="{{ asset($questionnaire->company_logo) }}" alt="{{ config('app.name') }}">
                        @elseif ( ! empty(config('project.default_logo_inverted')))
                            <img class="company-logo company-logo--page" src="{{ asset(config('project.default_logo_inverted')) }}" alt="{{ config('app.name') }}">
                        @endif

                        <span class="copyright aeonik14">&copy; {{ date('Y') }} {{ config('app.name') }}</span>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    @stack('javascript')

</body>

</html>
