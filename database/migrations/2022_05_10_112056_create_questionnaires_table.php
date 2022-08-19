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
        if (Schema::connection(((env('QUESTIONNAIRE_DATABASE') !== null && env('QUESTIONNAIRE_DATABASE') !== '') ? env('QUESTIONNAIRE_DATABASE') : 'mysql'))->hasTable('questionnaires')) {
            return;
        }

        Schema::connection(((env('QUESTIONNAIRE_DATABASE') !== null && env('QUESTIONNAIRE_DATABASE') !== '') ? env('QUESTIONNAIRE_DATABASE') : 'mysql'))->create('questionnaires', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('legal_page_id')->nullable();
            $table->string('progress_page_ids')->nullable();
            $table->string('handler_class');
            $table->string('company_name');
            $table->text('company_logo');
            $table->string('title');
            $table->string('slug');
            $table->text('intro')->nullable();
            $table->string('start_button_label')->nullable();
            $table->tinyInteger('time_indicator')->nullable();
            $table->string('questionnaire_owner_email');
            $table->boolean('is_active')->default(1);
            $table->boolean('show_progress_text')->default(1);
            $table->boolean('has_intro')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection(((env('QUESTIONNAIRE_DATABASE') !== null && env('QUESTIONNAIRE_DATABASE') !== '') ? env('QUESTIONNAIRE_DATABASE') : 'mysql'))->dropIfExists('questionnaires');
    }
};
