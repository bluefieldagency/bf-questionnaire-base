<?php

if ( ! function_exists('repo_path')) {
    function repo_path($addPath = '')
    {
        return base_path('vendor/immensenl/bf_questionnaire_base/src/' . ltrim($addPath, '/'));
    }
}

if ( ! function_exists('domain_route')) {
    function domain_route($languageCode, $name, $parameters = [])
    {
        $domain = env('URL_' . strtoupper($languageCode));

        return $domain . '/' . ltrim(route($name, $parameters, false), '/');
    }
}