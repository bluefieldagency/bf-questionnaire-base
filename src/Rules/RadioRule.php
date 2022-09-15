<?php

namespace Questionnaire\Rules;

use Questionnaire\Models\Page;
use Questionnaire\Models\Question;
use Illuminate\Contracts\Validation\Rule;

class RadioRule implements Rule
{

    public function __construct(
        protected Page $page,
        protected Question $question
    ) {}

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $answers = $this->question->answers->keyBy('id');

        if (isset($answers[request()->input('question_' . $this->question->id . '_answer')])) {
            return true;
        }

        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The selected answer does not belong to this question.';
    }
}
