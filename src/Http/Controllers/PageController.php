<?php

namespace Questionnaire\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Questionnaire\Jobs\SendNotifyQuestionnaireOwner;
use Questionnaire\Http\Requests\PageRequest;
use Questionnaire\Models\Page;
use Questionnaire\Models\Questionnaire;
use Questionnaire\Models\QuestionnaireEntry;
use Questionnaire\Models\QuestionnaireInvite;

class PageController extends Controller
{

    protected function getQuestionnaireCode()
    {
        return config('questionnaire.questionnaire_code');
    }

    public function index(Questionnaire $questionnaire, Page $page)
    {
        if ( ! $page->is_active) {
            abort(404);
        }

        if ($questionnaire->slug != $this->getQuestionnaireCode()) {
            abort(404);
        }

        if ($questionnaire->hasOption('requires_invite') && $questionnaire->getOption('requires_invite') && ! session()->has('questionnaire.invite_id')) {
            return redirect(route('requires-invite'));
        }

        if (session()->has('questionnaire.loaded_pages')) {
            $questionnaire->setRelation('pages', session('questionnaire.loaded_pages'));
        }

        // see if previous pages are filled
        foreach($questionnaire->pages as $questionnairePage) {
            if ($questionnairePage->id == $page->id) {
                break;
            }

            if ( ! session()->has('questionnaire.page.' . $questionnairePage->id)) {
                return redirect(route($questionnaire->getRouteNameFor('page'), [$questionnaire->slug, $questionnairePage->slug]));
            }
        }

        $page->load(['questions' => function($query) {
            $query->whereNull('parent_id');
        }, 'questions.children']);

        if ($questionnaire->getProgressStepThisPage($page) > 1) {
            // Determine the previous page, to have a previous step link
            $previousPage = $this->getPreviousPage($questionnaire, $page);
            $previousPageUrl = null;
            if ($previousPage) {
                $previousPageUrl = route($questionnaire->getRouteNameFor('page'), [$questionnaire->slug, $previousPage->slug]);
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

        if ( ! empty($questionnaire->handler_class)) {
            $handler = app($questionnaire->handler_class);

            if ($handler) {
                session(['handler_class' => $questionnaire->handler_class]);

                app()->instance('handler', $handler);

                \View::share('handler', $handler);
            }
        }

        return view($viewTemplate, compact('questionnaire', 'page', 'previousPageUrl', 'skipIterators'));
    }

    public function store(PageRequest $request, Questionnaire $questionnaire, Page $page)
    {
        session([('questionnaire.page.' . $page->id) => $request->except(array_merge(['_token'], array_keys($_FILES)))]);

        $page->load('questions.question_type');

        $this->handleUploadsForPage($page);

        $this->storeSpecificValues($request, $page);

        // Determine the next page to show to the attendee
        $nextPage = $this->getNextPage($questionnaire, $page);
        if ($nextPage) {
            return redirect(route($questionnaire->getRouteNameFor('page'), [$questionnaire->slug, $nextPage->slug]));
        }

        // No more pages to do? Then we are done!
        $this->completeQuestionnaire($questionnaire);

        return redirect(route($questionnaire->getRouteNameFor('completed'), [$questionnaire->slug]));
    }

    protected function storeSpecificValues($request, Page $page)
    {
        foreach (QuestionnaireEntry::$fixedDataTypes as $specificValue) {
            foreach ($page->questions as $question) {
                if (isset($question->options['data_type']) && $question->options['data_type'] == $specificValue) {
                    session([('questionnaire.' . $specificValue) => $request->input('question_' . $question->id . '_answer')]);
                }
            }
        }
    }

    protected function handleUploadsForPage(Page $page)
    {
        foreach($page->questions as $question) {
            $handleUploads = false;

            if (in_array($question->question_type->type, ['text', 'textarea'])) {
                if ($question->getOption('allow_additional_uploads') === true) {
                    $handleUploads = true;
                }
            } else if (in_array($question->question_type->type, ['file', 'multi_file'])) {
                $handleUploads = true;
            }

            if ($handleUploads && request()->hasFile('question_' . $question->id . '_answer_file')) {
                $counter = 0;
                if (session()->has('questionnaire.file.' . $page->id . '.' . $question->id)) {
                    foreach(session('questionnaire.file.' . $page->id . '.' . $question->id) as $key => $file) {
                        $counter = $key + 1;
                    }
                }

                foreach(request()->file('question_' . $question->id . '_answer_file') as $upload) {
                    if ($upload->isValid()) {
                        $upload->store('temp_attachments');

                        session([('questionnaire.file.' . $page->id . '.' . $question->id . '.' . $counter) => [
                            'original_name' => $upload->getClientOriginalName(),
                            'stored_as' => $upload->hashName(),
                        ]]);
                    }

                    $counter++;

                    if ($question->hasOption('additional_upload_max') && $question->getOption('additional_upload_max') < $counter) {
                        break;
                    }
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
        if (session()->has('questionnaire.loaded_pages')) {
            $questionnaire->setRelation('pages', session('questionnaire.loaded_pages'));
            $questionnairePages = $questionnaire->pages;
        } else {
            $questionnairePages = $questionnaire->pages()->ordered()->get();
        }

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
        if (session()->has('questionnaire.loaded_pages')) {
            $questionnaire->setRelation('pages', session('questionnaire.loaded_pages'));
        }

        if ( ! empty($questionnaire->handler_class)) {
            $handler = app($questionnaire->handler_class);
        }

        $questionnaire->loadMissing([
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
                if ( ! $handler || ($handler && ! $handler->showQuestion($question))) {
                    continue;
                }

                if ($question->hasOptions()) {
                    $score = 0;

                    if ($question->hasOption('data_type', 'score')) {
                        $scoreableQuestions++;

                        $answer = $questionnaireEntry->getAnswer($question);

                        if ($answer) {
                            if ($question->question_type->type == 'radio' || $question->question_type->type == 'stars') {
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
                            } else if ($question->question_type->type == 'range') {
                                $score = $answer;
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

        return [
            'scoreableQuestions' => $scoreableQuestions,
            'scoreTotal' => $scoreTotal,
            'scorePerCaterogryTotal' => $scorePerCaterogryTotal,
            'scoreableQuestionsPerCaterogry' => $scoreableQuestionsPerCaterogry,
            'averageScorePerCaterogry' => $averageScorePerCaterogry,
            'scorePerQuestion' => $scorePerQuestion,
        ];
    }

    protected function intermediateStoreQuestionnaire(Request $request, Questionnaire $questionnaire, Page $page)
    {
        session([('questionnaire.page.' . $page->id) => $request->except(array_merge(['_token'], array_keys($_FILES)))]);

        $questionnaireEntry = $this->storeEntry($questionnaire);

        $questionnaireEntry->progress = $this->calculateProgressPercentage($questionnaire);
        $questionnaireEntry->save();

        return new JsonResponse(['saved_as' => $questionnaireEntry->id]);
    }

    protected function calculateProgressPercentage($questionnaire)
    {
        $progress = $totalQuestionCount = $totalCount = 0;

        // a double loadCont does not work, so have to just load the models
        $questionnaire->load(['pages' => function($query) {
            $query->active();
        }, 'pages.questions' => function($query) {
            $query->active()->whereNull('parent_id');
        }]);

        foreach($questionnaire->pages as $questionnairePage) {
            $totalQuestionCount += sizeof($questionnairePage->questions);
        }

        if ($totalQuestionCount > 0) {
            foreach (session('questionnaire.page') as $entries) {
                foreach($entries as $entry) {
                    if ($entry !== null) {
                        $totalCount++;
                    }
                }
            }

            $progress = (($totalCount / $totalQuestionCount) * 100);
        }

        if ($progress > 100) {
            $progress = 100;
        }

        session([('questionnaire.progress') => $progress]);

        return $progress;
    }

    protected function completeQuestionnaire(Questionnaire $questionnaire)
    {
        $questionnaireEntry = $this->storeEntry($questionnaire);

        session([('questionnaire.filled.' . $questionnaireEntry->id) => $questionnaireEntry->id]);

        $questionnaireEntry->setScores($this->calculateScores($questionnaire, $questionnaireEntry));
        $questionnaireEntry->progress = 100;
        $questionnaireEntry->save();

        if (session()->has('questionnaire.invite_id')) {
            $questionnaireInvite = QuestionnaireInvite::find(session('questionnaire.invite_id'));
            if ($questionnaireInvite) {
                $questionnaireInvite->questionnaire_entry()->associate($questionnaireEntry);
                $questionnaireInvite->save();
            }
        }

        if ( ! empty($questionnaire->handler_class)) {
            $this->handler = app($questionnaire->handler_class);

            $this->handler->complete($questionnaire, $questionnaireEntry, $questionnaireEntry->getScores());
        }

        $this->notifyOwner($questionnaireEntry);

        Questionnaire::resetSession();
    }

    protected function storeEntry(Questionnaire $questionnaire)
    {
        if (session()->has('questionnaire.id')) {
            $questionnaireEntry = QuestionnaireEntry::find(session('questionnaire.id'));
        }

        if ( ! $questionnaireEntry) {
            $questionnaireEntry = new QuestionnaireEntry();
        }

        foreach (QuestionnaireEntry::$fixedDataTypes as $fixedDataType) {
            if (session()->has('questionnaire.' . $fixedDataType)) {
                $questionnaireEntry->setAttribute($fixedDataType, session('questionnaire.' . $fixedDataType));
            }
        }

        $questionnaireEntry->answers = json_encode(session('questionnaire.page'));

        if (Auth::user()) {
            // do not use associate, as that does not work with multiple databases
            $questionnaireEntry->user_id = Auth::user()->id;
        }
        $questionnaire->questionnaire_entries()->save($questionnaireEntry);

        session(['questionnaire.id' => $questionnaireEntry->id]);

        if (session()->has('questionnaire.file')) {
            foreach(Arr::dot(session('questionnaire')) as $key => $value) {
                if (Str::endsWith($key, '.stored_as')) {
                    Storage::move(('temp_attachments/' . $value), ('questionnaire_entries/' . $questionnaireEntry->id . '/' . $value));
                }
            }

            $questionnaireEntry->files = json_encode(session('questionnaire.file'));
        }

        return $questionnaireEntry;
    }

    protected function notifyOwner(QuestionnaireEntry $questionnaireEntry)
    {
        dispatch(new SendNotifyQuestionnaireOwner($questionnaireEntry));
    }

    public function removeFile(Request $request)
    {
        // make sure the file to be removed exists in this array (otherwise you could delete other peoples files)
        foreach(Arr::dot(session('questionnaire')) as $key => $value) {
            if ($value == $request->input('file')) {
                Storage::delete('temp_attachments/' . $request->file);
                session()->forget('questionnaire.' . Str::before($key, '.stored_as'));
            }
        }

        return new JsonResponse(['removed' => $request->file]);
    }

}
