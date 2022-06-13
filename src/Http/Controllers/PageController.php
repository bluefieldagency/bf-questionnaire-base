<?php

namespace Questionnaire\Http\Controllers;

use Questionnaire\Http\Requests\PageRequest;
use Questionnaire\Models\Page;
use Questionnaire\Models\Questionnaire;
use Questionnaire\Models\QuestionnaireEntry;

class PageController extends Controller
{

    public function index(Questionnaire $questionnaire, Page $page)
    {
        if ($questionnaire->getProgressStepThisPage($page) > 1) {
            // Determine the previous page, to have a previous step link
            $previousPage = $this->getPreviousPage($questionnaire, $page);
            $previousPageUrl = null;
            if ($previousPage) {
                $previousPageUrl = route('questionnaire.page', [$questionnaire->slug, $previousPage->slug]);
            }
        }

        return view('questionnaire::questionnaire.page', compact('questionnaire', 'page', 'previousPageUrl'));
    }

    public function store(PageRequest $request, Questionnaire $questionnaire, Page $page)
    {
        session([('questionnaire.page.' . $page->id) => $request->except('_token')]);

        $this->storeSpecificValues($request, $page);

        // Determine the next page to show to the attendee
        $nextPage = $this->getNextPage($questionnaire, $page);
        if ($nextPage) {
            return redirect(route('questionnaire.page', [$questionnaire->slug, $nextPage->slug]));
        }

        // No more pages to do? Then we are done!
        $this->completeQuestionnaire($questionnaire);

        return redirect(route('questionnaire.completed', [$questionnaire->slug]));
    }

    protected function storeSpecificValues($request, Page $page)
    {
        $specificValues = [
            'name',
            'email',
        ];

        foreach ($specificValues as $specificValue) {
            foreach ($page->questions as $question) {
                if (isset($question->options['data_type']) && $question->options['data_type'] == $specificValue) {
                    session([('questionnaire.' . $specificValue) => $request->input('question_' . $question->id . '_answer')]);
                }
            }
        }
    }

    protected function getPreviousPage(Questionnaire $questionnaire, Page $page)
    {
        return $this->getAdjecentPage($questionnaire, $page, 'previous');
    }

    protected function getNextPage(Questionnaire $questionnaire, Page $page)
    {
        return $this->getAdjecentPage($questionnaire, $page, 'next');
    }

    protected function getAdjecentPage(Questionnaire $questionnaire, Page $page, $direction)
    {
        $questionnairePages = $questionnaire->pages()->ordered()->get();

        $foundKey = null;
        foreach ($questionnairePages as $key => $questionnairePage) {
            if ($page->id == $questionnairePage->id) {
                $foundKey = $key;
            }
        }

        if ($direction == 'previous') {
            $foundKey--;
        } else {
            $foundKey++;
        }

        if (isset($questionnairePages[$foundKey])) {
            return $questionnairePages[$foundKey];
        }

        return null;
    }

    public function calculateScores(Questionnaire $questionnaire, QuestionnaireEntry $questionnaireEntry)
    {
        $questionnaire->load([
            'pages',
            'pages.questions',
            'pages.questions.answers',
            'pages.questions.question_category',
            'pages.questions.question_type',
        ]);

        $scoreTotal = 0;
        $scorePerQuestion = [];
        $scorePerCaterogryTotal = [];
        $scoreableQuestions = 0;
        $scoreableQuestionsPerCaterogry = [];
        $averageScorePerCaterogry = [];

        foreach($questionnaire->pages as $page) {
            foreach($page->questions as $question) {
                if ($question->hasOptions()) {
                    $score = 0;

                    if ($question->hasOption('data_type', 'score')) {
                        $scoreableQuestions++;

                        $answer = $questionnaireEntry->getAnswer($question);

                        if ($question->question_type->type == 'radio') {
                            $anwserModel = $question->getAnswer($answer);

                            $score = $anwserModel->getOption('score');
                        } else if ($question->question_type->type == 'checkbox') {
                            foreach($answer as $key => $value) {
                                $anwserModel = $question->getAnswer($key);

                                $score += $anwserModel->getOption('score');
                            }

                            if ($question->hasOption('score_max')) {
                                $scoreMax = $question->getOption('score_max');

                                if ($scoreMax > 0 & $score > $scoreMax) {
                                    $score = $scoreMax;
                                }
                            }
                        }

                        if ( ! isset($scorePerQuestion[$question->id])) {
                            $scorePerQuestion[$question->id] = $score;
                        }
                    }

                    $scoreTotal += $score;

                    if ($question->question_category) {
                        $categoryId = $question->question_category->id;

                        if ( ! isset($scorePerCaterogryTotal[$categoryId])) {
                            $scorePerCaterogryTotal[$categoryId] = 0;
                        }
                        $scorePerCaterogryTotal[$categoryId] += $score;

                        if ( ! isset($scoreableQuestionsPerCaterogry[$categoryId])) {
                            $scoreableQuestionsPerCaterogry[$categoryId] = 0;
                        }
                        $scoreableQuestionsPerCaterogry[$categoryId]++;

                        if ( ! isset($averageScorePerCaterogry[$categoryId])) {
                            $averageScorePerCaterogry[$categoryId] = 0;
                        }

                        $averageScorePerCaterogry[$categoryId] = round(($scorePerCaterogryTotal[$categoryId] / $scoreableQuestionsPerCaterogry[$categoryId]), 0);
                    }
                }
            }
        }

//        dd([
//            'scoreableQuestions' => $scoreableQuestions,
//            'scoreTotal' => $scoreTotal,
//            'scorePerCaterogryTotal' => $scorePerCaterogryTotal,
//            'scoreableQuestionsPerCaterogry' => $scoreableQuestionsPerCaterogry,
//            'averageScorePerCaterogry' => $averageScorePerCaterogry,
//            'scorePerQuestion' => $scorePerQuestion,
//        ]);

        return [
            'scoreableQuestions' => $scoreableQuestions,
            'scoreTotal' => $scoreTotal,
            'scorePerCaterogryTotal' => $scorePerCaterogryTotal,
            'scoreableQuestionsPerCaterogry' => $scoreableQuestionsPerCaterogry,
            'averageScorePerCaterogry' => $averageScorePerCaterogry,
            'scorePerQuestion' => $scorePerQuestion,
        ];
    }

    protected function completeQuestionnaire(Questionnaire $questionnaire)
    {
        $questionnaireEntry = $this->storeEntry($questionnaire);

        session([('questionnaire.filled.' . $questionnaireEntry->id) => $questionnaireEntry->id]);

        $questionnaireEntry->scores = $this->calculateScores($questionnaire, $questionnaireEntry);
        $questionnaireEntry->save();

        $this->handler = app($questionnaire->handler_class);

        $this->handler->complete($questionnaire, $scores);

        session()->forget([
            'questionnaire.name',
            'questionnaire.email',
            'questionnaire.page',
        ]);
    }

    protected function storeEntry(Questionnaire $questionnaire)
    {
        $questionnaireEntry = new QuestionnaireEntry();

        if (session()->has('questionnaire.name')) {
            $questionnaireEntry->name = session('questionnaire.name');
        }

        if (session()->has('questionnaire.email')) {
            $questionnaireEntry->email = session('questionnaire.email');
        }

        $questionnaireEntry->answers = json_encode(session('questionnaire.page'));

        $questionnaire->questionnaire_entries()->save($questionnaireEntry);

        return $questionnaireEntry;
    }

}
