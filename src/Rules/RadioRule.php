<?php

namespace App\Rules;

use App\Models\Page;
use App\Models\Question;
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
