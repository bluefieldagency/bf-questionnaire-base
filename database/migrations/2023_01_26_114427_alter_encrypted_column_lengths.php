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
        Schema::connection(((env('QUESTIONNAIRE_DATABASE') !== null && env('QUESTIONNAIRE_DATABASE') !== '') ? env('QUESTIONNAIRE_DATABASE') : 'mysql'))->table('questionnaire_entries', function (Blueprint $table) {
            $table->text('project_name')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // don't reverse, as that might truncate data
    }
};
