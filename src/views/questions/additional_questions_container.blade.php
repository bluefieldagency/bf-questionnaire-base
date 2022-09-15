<ul class="additional-questions-container">
    @foreach($question->children as $additionalQuestion)
        <li
            class="
                form-line
                form-line--child
                additional-question-container
                question-type--{{ $additionalQuestion->question_type->type }}
                @if ($additionalQuestion->hasOption('container_border') && $additionalQuestion->getOption('container_border') === false)
                    skip-borders
                @endif
                @if ($page->hasOption('container_border') && $page->getOption('container_border') === false)
                    skip-borders
                @endif
                @if ($question->is_required)
                    is-required
                @endif
            "
            data-answer_count="{{ sizeof($additionalQuestion->answers) }}"
            data-question_type="{{ $additionalQuestion->question_type->type }}"
            data-answer_trigger="{{ $additionalQuestion->getOption('answer_trigger') }}"
            data-question_id="{{ $additionalQuestion->id }}"
        >
            @include('questionnaire::questions.page_question_' . $additionalQuestion->question_type->type, [
                'question' => $additionalQuestion,
                'child' => true,
            ])
        </li>
    @endforeach
</ul>
