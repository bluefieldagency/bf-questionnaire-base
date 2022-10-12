<?php

namespace Questionnaire\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Questionnaire\Mail\NotifyQuestionnaireOwner;
use Questionnaire\Models\QuestionnaireEntry;

class SendNotifyQuestionnaireOwner implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(protected QuestionnaireEntry $questionnaireEntry) {}

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Mail::send(new NotifyQuestionnaireOwner($this->questionnaireEntry));
    }

}
