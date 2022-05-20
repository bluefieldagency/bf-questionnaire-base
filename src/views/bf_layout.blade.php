@section('header')
    <header>
        <div class="container">
            <div class="row">
                <div class="col-sm-12 header">
                    <img class="company-logo company-logo--page" src="{{ asset($questionnaire->company_logo) }}" alt="{{ $questionnaire->company_name }}">
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

    @yield('header')

    <div class="container">
        <div class="row">
            @yield('content')
        </div>
    </div>

    <footer></footer>

    @stack('javascript')

</body>

</html>