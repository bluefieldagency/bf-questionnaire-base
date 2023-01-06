<ul class="additional-questions-container @if($question->hasOption('child_triggered') && $question->getOption('child_triggered') === false) visible @endif">
    @foreach($question->children as $additionalQuestion)
        @if ( ! $handler || ($handler && $handler->showQuestion($additionalQuestion)))
            <li
                class="
                    form-line
                    form-line--child
                    additional-question-container
                    question-type--{{ $additionalQuestion->question_type->type }}
                    @if($question->hasOption('child_triggered') && $question->getOption('child_triggered') === false) visible @endif
                    @if ($additionalQuestion->hasOption('container_border') && $additionalQuestion->getOption('container_border') === false)
                        skip-borders
                    @endif
                    @if ($page->hasOption('container_border') && $page->getOption('container_border') === false)
                        skip-borders
                    @endif
                    @if ($additionalQuestion->is_required)
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
        @endif
    @endforeach
</ul>
