@if ( ! empty($page->title))
    <h1 class="page-title">{{ $page->title }}</h1>
@endif

@if ( ! empty($page->intro))
    <p class="page-intro">{!! $page->intro !!}</p>
@endif

@if ($questionnaire->show_progress_text && $questionnaire->showProgressForThisPage($page))
    @if ($questionnaire->getProgressPagesAmount() > 1)
        <h2 class="progress-text">@lang('bf::translations.step') <span id="current_indicator">{{ $questionnaire->getProgressStepThisPage($page) }}</span> @lang('bf::translations.of-the') {{ $questionnaire->getProgressPagesAmount() }}</h2>
    @else
        <h2 class="progress-text">@lang('bf::translations.question') <span id="current_indicator">1</span> @lang('bf::translations.of-the') {{ sizeof($page->questions) }}</h2>
    @endif
@endif

<form id="questionnaire_page_{{ $page->id }}"  method="POST" enctype="multipart/form-data">
    @csrf

    @if ($page->show_questions_numbered)
        <ol class="questions">
            @foreach($page->questions as $question)
                <li
                    class="
                        form-line
                        form-line--parent
                        question-container
                        question-type--{{ $question->question_type->type }}
                        @if ($loop->first)
                            current
                        @elseif( ! in_array($question->question_type->type, ['text', 'email']))
                            disabled
                        @endif
                        @if (sizeof($question->children))
                            has-children
                        @endif
                        @if ($question->hasOption('container_border') && $question->getOption('container_border') === false)
                            skip-borders
                        @endif
                        @if ($page->hasOption('container_border') && $page->getOption('container_border') === false)
                            skip-borders
                        @endif
                        @if ($question->is_required)
                            is-required
                        @endif
                    "
                    data-answer_count="{{ sizeof($question->answers) }}"
                    data-question_type="{{ $question->question_type->type }}"
                    data-question_id="{{ $question->id }}"
                    @if($skipIterators > 1 && $loop->first)
                        value="{{ $skipIterators }}"
                    @endif
                >
                    <div class="question-content-container">
                        @include('questionnaire::questions.page_question_' . $question->question_type->type)

                        @if (sizeof($question->children))
                            @include('questionnaire::questions.additional_questions_container')
                        @endif
                    </div>
                </li>
            @endforeach
        </ol>
    @else
        <div class="questions">
            @foreach($page->questions as $question)
                <div
                    class="
                        form-line
                        form-line--parent
                        question-container
                        question-type--{{ $question->question_type->type }}
                        @if ($loop->first)
                            current
                        @elseif( ! in_array($question->question_type->type, ['text', 'email']))
                            disabled
                        @endif
                        @if ($question->hasOption('container_border') && $question->getOption('container_border') === false)
                            skip-borders
                        @endif
                        @if ($page->hasOption('container_border') && $page->getOption('container_border') === false)
                            skip-borders
                        @endif
                        @if ($question->is_required)
                            is-required
                        @endif
                    "
                    data-answer_count="{{ sizeof($question->answers) }}"
                    data-question_type="{{ $question->question_type->type }}"
                    data-question_id="{{ $question->id }}"
                >
                    <div class="question-content-container">
                        @include('questionnaire::questions.page_question_' . $question->question_type->type)

                        @if (sizeof($question->children))
                            @include('questionnaire::questions.additional_questions_container')
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <div
        class="buttons-container
            @if (isset($previousPageUrl) && $previousPageUrl != '') buttons-container--flex @endif
            @if ($page->show_questions_numbered) questions-numbered @endif
        ">
        @if (isset($previousPageUrl) && $previousPageUrl != '')
            <a class="large-link previous-page-link" href="{{ $previousPageUrl }}">@lang('bf::translations.previous-step')</a>
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