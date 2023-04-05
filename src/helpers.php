<?php

if ( ! function_exists('repo_path')) {
    function repo_path($addPath = '')
    {
        return base_path('vendor/bluefieldagency/bf-questionnaire-base/src/' . ltrim($addPath, '/'));
    }
}

if ( ! function_exists('domain_route')) {
    function domain_route($languageCode, $name, $parameters = [])
    {
        $domain = env('URL_' . strtoupper($languageCode));

        return $domain . '/' . ltrim(route($name, $parameters, false), '/');
    }
}

if ( ! function_exists('save_resolve')) {
    function save_resolve($name, array $parameters = [], $default = null)
    {
        try {
            return app()->make($name);
        } catch (\Illuminate\Contracts\Container\BindingResolutionException $e) {
            return $default;
        }
    }
}
