@extends('questionnaire::bf_layout')

@section('header')
    <header>
        <div class="wrapper">
            <div class="header">
                <img class="company-logo company-logo--page" src="{{ asset($questionnaire->company_logo) }}" alt="{{ $questionnaire->company_name }}">
            </div>
        </div>
    </header>
@endsection

@section('content')

    <div id="questionnaire_page">
        <form id="questionnaire_page_{{ $page->id }}" method="POST">
            @csrf

            @foreach($page->questions as $question)
                <div class="form-line">
                    <h2>{{ $question->title }}</h2>

                    @if ( ! empty($question->extra_info))
                        <p>{{ $question->extra_info }}</p>
                    @endif

                    <div class="error-form">{{ $errors->first('question_' . $question->id . '_answer') }}</div>

                    @include('questionnaire::question_types.' . $question->question_type->type)
                </div>
            @endforeach

            @component('questionnaire::components.button')
                @slot('type')
                    submit
                @endslot

                @slot('label')
                    {{ $page->continue_button_label ?? __('Continue questionnaire') }}
                @endslot
            @endcomponent
        </form>
    </div>

@endsection