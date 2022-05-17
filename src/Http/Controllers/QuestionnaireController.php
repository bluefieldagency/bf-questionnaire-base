<?php

namespace Bluefield\Questionnaire\Http\Controllers;

use Bluefield\Questionnaire\Models\Questionnaire;

class QuestionnaireController extends Controller
{

    public function index(Questionnaire $questionnaire)
    {
        $page = $questionnaire->pages()->ordered()->first();

        $url = route('questionnaire.page', [$questionnaire->slug, $page->slug]);

        return view('questionnaire.intro', compact('questionnaire', 'url'));
    }

}
