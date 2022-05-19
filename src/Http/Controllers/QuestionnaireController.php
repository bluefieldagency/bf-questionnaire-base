<?php

namespace Questionnaire\Http\Controllers;

use Questionnaire\Models\Questionnaire;

class QuestionnaireController extends Controller
{

    public function redirect()
    {
        if (empty(config('questionnaire.questionnaire_code')) || ! config('questionnaire.questionnaire_code')) {
            exit('No questionnaire set');
        }
        
        $questionnaire = Questionnaire::where('slug', config('questionnaire.questionnaire_code'))->firstOrFail();

        return redirect(route('questionnaire.intro', [$questionnaire]));
    }

    public function index(Questionnaire $questionnaire)
    {
        $page = $questionnaire->pages()->ordered()->first();

        $url = route('questionnaire.page', [$questionnaire->slug, $page->slug]);

        return view('questionnaire.intro', compact('questionnaire', 'url'));
    }

}
