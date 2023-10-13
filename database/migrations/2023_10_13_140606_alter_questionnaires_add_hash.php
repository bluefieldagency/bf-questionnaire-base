<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Questionnaire\Models\Questionnaire;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::connection(((env('QUESTIONNAIRE_DATABASE') !== null && env('QUESTIONNAIRE_DATABASE') !== '') ? env('QUESTIONNAIRE_DATABASE') : 'mysql'))->hasColumn('questionnaires', 'hash')) {
            return;
        }

        Schema::connection(((env('QUESTIONNAIRE_DATABASE') !== null && env('QUESTIONNAIRE_DATABASE') !== '') ? env('QUESTIONNAIRE_DATABASE') : 'mysql'))->table('questionnaires', function (Blueprint $table) {
            $table->string('hash')->after('questionnaire_owner_email')->nullable();
        });

        foreach(Questionnaire::whereNull('hash')->get() as $questionnaire) {
            $questionnaire::generateHash($questionnaire);
            $questionnaire->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection(((env('QUESTIONNAIRE_DATABASE') !== null && env('QUESTIONNAIRE_DATABASE') !== '') ? env('QUESTIONNAIRE_DATABASE') : 'mysql'))->table('questionnaires', function (Blueprint $table) {
            $table->dropColumn('hash');
        });;
    }
};
