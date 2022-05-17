@extends('bf_layout')

@section('content')

    <img class="company-logo" src="{{ asset($questionnaire->company_logo) }}" alt="{{ $questionnaire->company_name }}">

    <h1>{{ __('Hi') }} {{ $questionnaireEntry->name }}!</h1>
    <h2>{{ __('Bedankt!') }}</h2>

@endsection