<?php

namespace Questionnaire\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Questionnaire\Models\Page;
use Questionnaire\Models\Questionnaire;

class QuestionnaireController extends Controller
{

    protected function getQuestionnaireCode()
    {
        return config('questionnaire.questionnaire_code');
    }

    public function redirect()
    {
        $this->checkForQuestionnaireCode();
        
        $questionnaire = Questionnaire::where('slug', $this->getQuestionnaireCode())
            ->with(['pages' => function($query) {
                $query->active();
            }])
            ->firstOrFail();

        if (Auth::user()) {
            session([
                'questionnaire.name' => Auth::user()->name,
                'questionnaire.email' => Auth::user()->email,
            ]);
        }

        if ($questionnaire->has_intro) {
            if ($questionnaire->hasOption('requires_invite') && $questionnaire->getOption('requires_invite') && ! session()->has('questionnaire.invite_id')) {
                return redirect(route('requires-invite'));
            }

            return redirect(route($questionnaire->getRouteNameFor('intro'), [$questionnaire]));
        }

        return redirect($this->firstPageUrl($questionnaire));
    }

    public function startAgain()
    {
        Questionnaire::resetSession();

        return redirect(route('home'));
    }

    public function specificPage($pageCode)
    {
        $this->checkForQuestionnaireCode();

        $page = Page::where('slug', $pageCode)
            ->whereHas('questionnaire', function($query) {
                $query->where('slug', $this->getQuestionnaireCode());
            })
            ->with('questionnaire')
            ->firstOrFail();

        return redirect(route($page->questionnaire->getRouteNameFor('page'), [$page->questionnaire->slug, $page->slug]));
    }

    public function index(Questionnaire $questionnaire)
    {
        $url = $this->firstPageUrl($questionnaire);

        return view('questionnaire.intro', compact('questionnaire', 'url'));
    }

    public function firstPageUrl(Questionnaire $questionnaire)
    {
        $page = $questionnaire->pages()->active()->ordered()->first();

        return route($questionnaire->getRouteNameFor('page'), [$questionnaire->slug, $page->slug]);
    }

    protected function checkForQuestionnaireCode()
    {
        if (empty($this->getQuestionnaireCode()) || ! $this->getQuestionnaireCode()) {
            exit('No questionnaire set');
        }
    }

}
