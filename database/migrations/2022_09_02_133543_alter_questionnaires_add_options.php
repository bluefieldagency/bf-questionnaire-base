<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::connection(((env('QUESTIONNAIRE_DATABASE') !== null && env('QUESTIONNAIRE_DATABASE') !== '') ? env('QUESTIONNAIRE_DATABASE') : 'mysql'))->hasColumn('questionnaires', 'options')) {
            return;
        }

        Schema::connection(((env('QUESTIONNAIRE_DATABASE') !== null && env('QUESTIONNAIRE_DATABASE') !== '') ? env('QUESTIONNAIRE_DATABASE') : 'mysql'))->table('questionnaires', function (Blueprint $table) {
            $table->json('options')->after('questionnaire_owner_email')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection(((env('QUESTIONNAIRE_DATABASE') !== null && env('QUESTIONNAIRE_DATABASE') !== '') ? env('QUESTIONNAIRE_DATABASE') : 'mysql'))->table('questionnaires', function (Blueprint $table) {
            $table->dropColumn('options');
        });;
    }
};
