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

        if ( ! empty($this->page->questionnaire->handler_class)) {
            $handler = app($this->page->questionnaire->handler_class);
        }

        $validations = [];

        if (isset($this->page)) {
            foreach($this->page->questions as $question) {
                if ( ! $handler || ($handler && ! $handler->showQuestion($question))) {
                    continue;
                }

                $validations['question_' . $question->id . '_answer'] = $this->forQuestion($question);

                if (sizeof($question->children)) {
                    $answers = $question->answers->keyBy('id');

                    $answerId = request()->input('question_' . $question->id . '_answer');
                    if ($question->question_type->type == 'checkbox') {
                        $answerId = key($answerId);
                    }

                    if ($answerId && isset($answers[$answerId])) {
                        $answer = $answers[$answerId];

                        if ($answer->hasOption('data_type')) {
                            $dataType = $answer->getOption('data_type');

                            foreach($question->children as $child) {
                                if ( ! $handler || ($handler && ! $handler->showQuestion($child))) {
                                    continue;
                                }

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
        $type = $question->question_type->type;

        $ruleClass = 'Questionnaire\\Rules\\' . ucfirst($type) . 'Rule';

        $rules = [new $ruleClass($this->page, $question)];

        if ($question->is_required) {
            $rules[] = 'required';
        }

        if ($type == 'file') {
            $rules[] = 'mimes:jpg,jpeg,png,tiff,doc,docx,xls,xlsx,rtf,pdf';
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
