<?php

namespace Questionnaire\Http\Middleware;

use Questionnaire\Models\QuestionnaireInvite;

class QuestionnaireModels
{

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, \Closure $next)
    {
        $questionnaireInvite = null;
        if (session()->has('questionnaire.invite_id')) {
            $questionnaireInvite = QuestionnaireInvite::find(session('questionnaire.invite_id'));
        }

        app()->instance('questionnaireInviteModel', $questionnaireInvite);

        return $next($request);
    }

}
