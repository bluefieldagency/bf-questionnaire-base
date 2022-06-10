@if ( ! empty($page->title))
    <h1 class="page-title">{{ $page->title }}</h1>
@endif

@if ( ! empty($page->intro))
    <p class="page-intro">{!! $page->intro !!}</p>
@endif

@if ($questionnaire->show_progress_text)
    <h2 class="progress-text">Vraag 1 van de 9</h2>
@endif

<form id="questionnaire_page_{{ $page->id }}" method="POST">
    @csrf

    <ol class="questions">
        @foreach($page->questions as $question)
            <li class="form-line question-container question-type--{{ $question->question_type->type }} @if ($loop->first) current @endif">
                <div class="question-content-container">
                    <h4><label for="question_{{ $question->id }}_answer">{{ $question->title }}</label></h4>

                    @if ($question->hasOption('extra_info'))
                        <p class="extra-info">{{ $question->getOption('extra_info') }}</p>
                    @endif

                    <div class="error-form">{{ $errors->first('question_' . $question->id . '_answer') }}</div>

                    @include('questionnaire::question_types.' . $question->question_type->type)
                </div>
            </li>
        @endforeach
    </ol>

    @component('questionnaire::components.button')
        @slot('type')
            submit
        @endslot

        @slot('label')
            {{ $page->continue_button_label ?? __('Continue questionnaire') }}
        @endslot
    @endcomponent
</form>