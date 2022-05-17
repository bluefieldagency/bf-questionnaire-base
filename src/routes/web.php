<?php

use App\Http\Controllers\CompletedController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\QuestionnaireController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::name('questionnaire.')->group(function() {
    Route::get('questionnaire/{questionnaire:slug}', [QuestionnaireController::class, 'index'])->name('intro');
    Route::get('questionnaire/{questionnaire:slug}/completed', [CompletedController::class, 'index'])->name('completed');

    Route::get('questionnaire/{questionnaire:slug}/{page:slug}', [PageController::class, 'index'])->name('page');
    Route::post('questionnaire/{questionnaire:slug}/{page:slug}', [PageController::class, 'store']);
});