<?php

namespace Questionnaire\Rules;

use Questionnaire\Models\Page;
use Questionnaire\Models\Question;
use Illuminate\Contracts\Validation\Rule;

class NumberRule implements Rule
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
        if (is_numeric($value)) {
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
        return 'Please enter a valid number';
    }
}
