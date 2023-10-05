<?php

namespace Questionnaire\Handlers;

use Questionnaire\Models\Question;
use Questionnaire\Models\Questionnaire;
use Questionnaire\Models\QuestionnaireEntry;
use Questionnaire\Models\QuestionnaireInvite;
use Questionnaire\Traits\MailQuestionnaire;
use Illuminate\Routing\Router;

class BaseHandler
{

    use MailQuestionnaire;

    protected $questionnaire;

    public function init(Questionnaire $questionnaire) {}

    public function intermediateCheck(Questionnaire $questionnaire) {}

    public function complete(Questionnaire $questionnaire, QuestionnaireEntry $questionnaireEntry, $scores) {}

    public function setQuestionnaire(Questionnaire $questionnaire)
    {
        $this->questionnaire = $questionnaire;
    }

    public function setQuestionnaireInvite(QuestionnaireInvite $questionnaireInvite)
    {
        $this->questionnaireInvite = $questionnaireInvite;
    }

    public function showQuestion(Question $question)
    {
//        if ($question->question_type->type == 'hidden') {
//            return false;
//        }

        return true;
    }

    public function enrichTitle($model, $value)
    {
        if ($model->hasOption('replace_value_logic')) {
            foreach($model->getOption('replace_value_logic') as $key => $logic) {
                foreach($logic as $type => $properties) {
                    if ($type == 'question') {
                        foreach($properties as $questionId => $options) {
                            if (session()->has('questionnaire.hidden_inputs.' . $questionId)) {
                                foreach($options as $valueTrigger => $replacement) {
                                    if ($valueTrigger == session('questionnaire.hidden_inputs.' . $questionId) && isset($options[session('questionnaire.hidden_inputs.' . $questionId)])) {
                                        $value = str_replace(('[' . $key . ']'), $options[session('questionnaire.hidden_inputs.' . $questionId)], $value);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return preg_replace('/\[[^\]]*\]/', '', $value);
    }

}
