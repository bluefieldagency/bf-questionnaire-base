<?php

namespace Questionnaire\Http\Controllers;

use Questionnaire\Jobs\SendNotifyQuestionnaireOwner;
use Questionnaire\Http\Requests\PageRequest;
use Questionnaire\Models\Page;
use Questionnaire\Models\Question;
use Questionnaire\Models\Questionnaire;
use Questionnaire\Models\QuestionnaireEntry;

class PageController extends Controller
{

    public function index(Questionnaire $questionnaire, Page $page)
    {
        if ( ! $page->is_active) {
            abort(404);
        }

        if ($questionnaire->slug != config('questionnaire.questionnaire_code')) {
            abort(404);
        }

        // see if previous pages are filled
        for($i = 0; $i < $questionnaire->pages->count(); $i++) {
            $questionnairePage = $questionnaire->pages[$i];
            if ($questionnairePage->is_active && $questionnairePage->id == $page->id) {
                $i = $questionnaire->pages->count();
            }
            if ($questionnairePage->is_active && $questionnairePage->id != $page->id) {
                if ( ! session()->has('questionnaire.page.' . $questionnairePage->id)) {
                    return redirect(route('questionnaire.page', [$questionnaire->slug, $questionnairePage->slug]));
                }
            }
        }

        if ($questionnaire->getProgressStepThisPage($page) > 1) {
            // Determine the previous page, to have a previous step link
            $previousPage = $this->getPreviousPage($questionnaire, $page);
            $previousPageUrl = null;
            if ($previousPage) {
                $previousPageUrl = route('questionnaire.page', [$questionnaire->slug, $previousPage->slug]);
            }
        }

        $viewTemplate = 'questionnaire::questionnaire.page';
        if ( ! empty($page->custom_view_template)) {
            $viewTemplate = $page->custom_view_template;
        }

        $skipIterators = 1;
        if ($questionnaire->showProgressForThisPage($page) && $questionnaire->getProgressStepThisPage($page) > 1) {
            $progressPages = $questionnaire->getProgressPages();
            for($i = 0; $i < ($questionnaire->getProgressStepThisPage($page) - 1); $i++) {
                $skipIterators += $progressPages[$i]->questions->count();
            }
        }

        return view($viewTemplate, compact('questionnaire', 'page', 'previousPageUrl', 'skipIterators'));
    }

    public function store(PageRequest $request, Questionnaire $questionnaire, Page $page)
    {
        session([('questionnaire.page.' . $page->id) => $request->except(array_merge(['_token'], array_keys($_FILES)))]);

        $page->load('questions.question_type');

        $handleUploads = false;
        foreach($page->questions as $question) {
            if (in_array($question->question_type->type, ['text', 'textarea'])) {
                if ($question->getOption('allow_additional_uploads') === true) {
                    $handleUploads = true;
                }
            } else if (in_array($question->question_type->type, ['file', 'multi_file'])) {
                $handleUploads = true;
            }
        }

        $this->handleUploads($page, $question);

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

    protected function handleUploads(Page $page, Question $question)
    {
        if (request()->hasFile('question_' . $question->id . '_answer_file')) {
            $counter = 0;

            foreach(request()->file('question_' . $question->id . '_answer_file') as $upload) {
                if ($upload->isValid()) {
                    $file = $upload->store('temp_attachments');

                    session([('questionnaire.page.' . $page->id . '.file.' . $question->id . '.' . $counter) => [
                        'original_name' => $upload->getClientOriginalName(),
                        'stored_as' => $file,
                    ]]);
                }

                $counter++;

                if ($question->hasOption('additional_upload_max') && $question->getOption('additional_upload_max') < $counter) {
                    break;
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

        $questionnaireEntry->setScores($this->calculateScores($questionnaire, $questionnaireEntry));
        $questionnaireEntry->save();

        $this->handler = app($questionnaire->handler_class);

        $this->handler->complete($questionnaire, $questionnaireEntry, $scores);

        $this->notifyOwner($questionnaireEntry);

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

    protected function notifyOwner(QuestionnaireEntry $questionnaireEntry)
    {
        dispatch(new SendNotifyQuestionnaireOwner($questionnaireEntry));
    }

}
