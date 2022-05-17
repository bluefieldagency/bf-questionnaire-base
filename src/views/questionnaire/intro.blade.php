@extends('bf_layout')

@section('content')

    <div id="intro">
        <img class="company-logo" src="{{ asset($questionnaire->company_logo) }}"
             alt="{{ $questionnaire->company_name }}">

        <h1>{{ $questionnaire->company_name }}</h1>
        <h2>{{ $questionnaire->title }}</h2>
        <p>{{ $questionnaire->intro }}</p>

        @component('components.button-link')
            @slot('href')
                {{ $url }}
            @endslot

            @slot('label')
                {{ $questionnaire->start_button_label }}
            @endslot
        @endcomponent
    </div>

@endsection