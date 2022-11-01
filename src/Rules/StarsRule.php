<?php

namespace Questionnaire\Rules;

use Questionnaire\Models\Page;
use Questionnaire\Models\Question;
use Illuminate\Contracts\Validation\Rule;

class StarsRule implements Rule
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
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The validation error message.';
    }
}
