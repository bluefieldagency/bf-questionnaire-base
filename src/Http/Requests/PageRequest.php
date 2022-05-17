<?php

namespace App\Http\Requests;

use App\Rules\CheckboxRule;
use Illuminate\Foundation\Http\FormRequest;

class PageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        // Get bound Booking model from route
        $page = $this->route('page');
        $page->loadMissing('questions.question_type');

        $validations = [];

        if (isset($page)) {
            foreach($page->questions as $question) {
                $type = ucfirst($question->question_type->type);

                $ruleClass = 'App\\Rules\\' . $type . 'Rule';

                $rules = [new $ruleClass($page, $question)];

                if ($question->is_required) {
                    $rules[] = 'required';
                }

                $validations['question_' . $question->id . '_answer'] = $rules;
            }
        }

        return $validations;
    }
}
