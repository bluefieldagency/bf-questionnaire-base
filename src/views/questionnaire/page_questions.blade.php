@if ( ! empty($page->title))
    <h1 class="page-title">{{ $page->title }}</h1>
@endif

@if ( ! empty($page->intro))
    <p class="page-intro">{!! $page->intro !!}</p>
@endif

@if ($questionnaire->show_progress_text && $questionnaire->showProgressForThisPage($page))
    @if ($questionnaire->getProgressPagesAmount() > 1)
        <h2 class="progress-text">Stap <span id="current_indicator">{{ $questionnaire->getProgressStepThisPage($page) }}</span> van de {{ $questionnaire->getProgressPagesAmount() }}</h2>
    @else
        <h2 class="progress-text">Vraag <span id="current_indicator">1</span> van de {{ sizeof($page->questions) }}</h2>
    @endif
@endif

<form id="questionnaire_page_{{ $page->id }}" method="POST">
    @csrf

    @if ($page->show_questions_numbered)
        <ol class="questions">
            @foreach($page->questions as $question)
                <li class="form-line question-container question-type--{{ $question->question_type->type }} @if ($loop->first) current @elseif( ! in_array($question->question_type->type, ['text', 'email'])) disabled @endif" data-answer_count="{{ sizeof($question->answers) }}" data-question_type="{{ $question->question_type->type }}">
                    <div class="question-content-container">
                        @include('questionnaire::questions.page_question_' . $question->question_type->type)
                    </div>
                </li>
            @endforeach
        </ol>
    @else
        @foreach($page->questions as $question)
            <div class="form-line question-container question-type--{{ $question->question_type->type }} @if ($loop->first) current @elseif( ! in_array($question->question_type->type, ['text', 'email'])) disabled @endif" data-answer_count="{{ sizeof($question->answers) }}" data-question_type="{{ $question->question_type->type }}">
                @include('questionnaire::questions.page_question_' . $question->question_type->type)
            </div>
        @endforeach
    @endif

    <div class="buttons-container @if (isset($previousPageUrl) && $previousPageUrl != '') buttons-container--flex @endif">
        @if (isset($previousPageUrl) && $previousPageUrl != '')
            <a class="large-link previous-page-link" href="{{ $previousPageUrl }}">Vorige stap</a>
        @endif

        @component('questionnaire::components.button')
            @slot('type')
                submit
            @endslot

            @slot('extra_classes')
                disabled submit-button
            @endslot

            @slot('label')
                {{ $page->continue_button_label ?? __('Continue questionnaire') }}
            @endslot
        @endcomponent

        @stack('email_disclaimer')
    </div>
</form>