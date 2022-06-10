@if ( ! empty($page->title))
    <h1 class="page-title">{{ $page->title }}</h1>
@endif

@if ( ! empty($page->intro))
    <p class="page-intro">{!! $page->intro !!}</p>
@endif

@if ($questionnaire->show_progress_text && $questionnaire->showProgressForThisPage($page))
    @if ($questionnaire->getProgressPagesAmount() > 1)
        <h2 class="progress-text">Vraag 1 van de {{ $questionnaire->getProgressPagesAmount() }}</h2>
    @else
        <h2 class="progress-text">Vraag 1 van de 9</h2>
    @endif
@endif

<form id="questionnaire_page_{{ $page->id }}" method="POST">
    @csrf

    @if ($page->show_questions_numbered)
        <ol class="questions">
            @foreach($page->questions as $question)
                <li class="form-line question-container question-type--{{ $question->question_type->type }} @if ($loop->first) current @else disabled @endif">
                    <div class="question-content-container">
                        @include('questionnaire::questionnaire.page_question')
                    </div>
                </li>
            @endforeach
        </ol>
    @else
        @foreach($page->questions as $question)
            <div class="form-line question-container question-type--{{ $question->question_type->type }} @if ($loop->first) current @else disabled @endif">
                @include('questionnaire::questionnaire.page_question')
            </div>
        @endforeach
    @endif

    <div class="buttons-container">
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