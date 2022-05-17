<?php

function repo_path($addPath = '') {
    return base_path('vendor/immensenl/bf_questionnaire_base/src/' . ltrim($addPath, '/'));
}