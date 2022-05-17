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

    <div class="wrapper">
        @yield('content')
    </div>

    <footer></footer>

</body>

</html>