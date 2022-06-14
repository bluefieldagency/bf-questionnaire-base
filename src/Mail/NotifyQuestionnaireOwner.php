<?php

namespace Questionnaire\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Questionnaire\Models\QuestionnaireEntry;

class NotifyQuestionnaireOwner extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(protected QuestionnaireEntry $questionnaireEntry) {}

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $this->questionnaireEntry->load([
            'questionnaire.pages.questions',
            'questionnaire.pages.questions.question_type',
            'questionnaire.pages.questions.answers',
        ]);

        $questions = [];
        $answers = [];
        foreach($this->questionnaireEntry->questionnaire->pages as $page) {
            foreach($page->questions as $question) {
                $questions[$question->id] = $question;

                foreach($question->answers as $answer) {
                    $answers[$answer->id] = $answer;
                }
            }
        }

        $result = [];
        foreach($this->questionnaireEntry->getAnswers() as $pageIterator => $entries) {
            $result[$pageIterator] = [];
            foreach($entries as $askedQuestion => $givenAnswer) {
                $data = [];
                $askedQuestionId = str_replace(['question_', '_answer'], '', $askedQuestion);
                if (isset($questions[$askedQuestionId])) {
                    $thisQuestion = $questions[$askedQuestionId];

                    $data = [
                        'question' => $questions[$askedQuestionId],
                    ];

                    if (in_array($thisQuestion->question_type->type, ['text', 'email'])) {
                        $data['answer'] = $givenAnswer;
                    } else if ($thisQuestion->question_type->type == 'radio') {
                        $data['answer'] = $answers[$givenAnswer]->title;
                    } else if ($thisQuestion->question_type->type == 'checkbox') {
                        $answerArray = [];
                        if (is_array($givenAnswer)) {
                            foreach($givenAnswer as $answerId => $boolean) {
                                $answerArray[] = $answers[$answerId]->title;
                            }
                        } else {
                            $answerArray[] = $answers[$givenAnswer]->title;
                        }

                        $data['answer'] = implode(', ', $answerArray);
                    }
                }
                $result[$pageIterator][] = $data;
            }
            $givenAnswer = [];
            $data[$pageIterator] = [];
        }

        return $this->view('questionnaire::mail.notify_owner', ['questionnaireEntry' => $this->questionnaireEntry])
            ->subject('Data Scan ingevuld door ' . $this->questionnaireEntry->name)
            ->with([
                'questionnaire' => $this->questionnaireEntry->questionnaire,
                'questionnaire_entry' => $this->questionnaireEntry,
                'questions' => $questions,
                'answers' => $answers,
                'result' => $result,
            ]);
    }
}
