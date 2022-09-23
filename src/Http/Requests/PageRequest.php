<?php

namespace Questionnaire\Http\Requests;

use Questionnaire\Rules\CheckboxRule;
use Illuminate\Foundation\Http\FormRequest;

class PageRequest extends FormRequest
{

    protected $page;

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
        $this->page = $this->route('page');
        $this->page->loadMissing([
            'questions' => function($query) {
                $query->whereNull('parent_id');
            },
            'questions.question_type',
            'questions.answers',
            'questions.children.question_type'
        ]);

        $validations = [];

        if (isset($this->page)) {
            foreach($this->page->questions as $question) {
                $validations['question_' . $question->id . '_answer'] = $this->forQuestion($question);

                if (sizeof($question->children)) {
                    $answers = $question->answers->keyBy('id');

                    $answerId = request()->input('question_' . $question->id . '_answer');
                    if (isset($answers[$answerId])) {
                        $answer = $answers[$answerId];

                        if ($answer->hasOption('data_type')) {
                            $dataType = $answer->getOption('data_type');

                            foreach($question->children as $child) {
                                if ($child->hasOption('answer_trigger') && $child->getOption('answer_trigger') == $dataType) {
                                    $validations['question_' . $child->id . '_answer'] = $this->forQuestion($child);
                                } else {
                                    $this->request->remove('question_' . $child->id . '_answer');
                                }
                            }
                        }
                    }
                }
            }
        }

        return $validations;
    }

    protected function forQuestion($question)
    {
        $type = ucfirst($question->question_type->type);

        $ruleClass = 'Questionnaire\\Rules\\' . $type . 'Rule';

        $rules = [new $ruleClass($this->page, $question)];

        if ($question->is_required) {
            $rules[] = 'required';
        }

        return $rules;
    }

    public function messages()
    {
        $messages = [];

        foreach($this->page->questions as $question) {
            $messages['question_' . $question->id . '_answer.required'] = 'Veld "' . $question->title . '" is verplicht';

            foreach($question->children as $child) {
                $messages['question_' . $child->id . '_answer.required'] = 'Veld "' . $child->title . '" is verplicht';
            }
        }

        return $messages;
    }

}
