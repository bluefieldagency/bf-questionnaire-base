<?php

namespace Questionnaire\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Questionnaire\Traits\OptionsTrait;
use Questionnaire\Factories\QuestionnaireEntryFactory;

class QuestionnaireEntry extends Model
{

    use HasFactory;
    use SoftDeletes;
    use OptionsTrait;

    protected $givenAnswers = null;
    protected $givenFiles = null;
    protected $scoredScores = null;

    protected $fillable = [
        'name',
        'email',
        'project_name',
        'answers',
        'files',
        'scores',
        'progress',
        'options',
    ];

    protected $casts = [
        'options' => AsCollection::class,
        'name' => 'encrypted',
        'email' => 'encrypted',
        'project_name' => 'encrypted',
        'answers' => 'encrypted',
        'files' => 'encrypted:collection',
    ];

    static public array $fixedDataTypes = [
        'name',
        'email',
        'project_name'
    ];

    public function __construct(array $attributes = [])
    {
        $this->setConnection(((env('QUESTIONNAIRE_DATABASE') !== null && env('QUESTIONNAIRE_DATABASE') !== '') ? env('QUESTIONNAIRE_DATABASE') : 'mysql'));

        parent::__construct($attributes);
    }

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        return QuestionnaireEntryFactory::new();
    }

    public function questionnaire() : BelongsTo
    {
        return $this->belongsTo(Questionnaire::class);
    }

    public function user() : BelongsTo
    {
        return $this->setConnection('mysql')->belongsTo(User::class);
    }

    public function questionnaire_invite() : HasOne
    {
        return $this->hasOne(QuestionnaireInvite::class);
    }

    public function getAnswers()
    {
        if ( ! $this->givenAnswers) {
            $this->givenAnswers = json_decode($this->answers, true);
        }

        return $this->givenAnswers;
    }

    public function getAnswer($question)
    {
        $this->getAnswers();

        if (isset($this->givenAnswers[$question->page_id]) && isset($this->givenAnswers[$question->page_id]['question_' . $question->id . '_answer'])) {
            return $this->givenAnswers[$question->page_id]['question_' . $question->id . '_answer'];
        }

        return null;
    }

    public function getFiles()
    {
        if ( ! $this->givenFiles) {
            $this->givenFiles = json_decode($this->files, true);
        }

        return $this->givenFiles;
    }

    public function getFile($question)
    {
        $this->getFiles();

        if (isset($this->givenFiles[$question->page_id]) && isset($this->givenFiles[$question->page_id][$question->id])) {
            return $this->givenFiles[$question->page_id][$question->id];
        }

        return null;
    }

    public function getScores()
    {
        if ( ! $this->scoredScores) {
            $this->scoredScores = json_decode($this->scores, true);
        }

        return $this->scoredScores;
    }

    public function getScore($category)
    {
        $this->getScores();

        if (isset($this->scoredScores[$category])) {
            return $this->scoredScores[$category];
        }

        return 0;
    }

    public function hasScore($category)
    {
        $this->getScores();

        if (isset($this->scoredScores[$category])) {
            return true;
        }

        return false;
    }

    public function setScores($scores)
    {
        $this->scores = json_encode($scores);

        $this->scoredScores = $scores;
    }

    public function setScore($key, $value)
    {
        $this->getScores();

        $this->scoredScores[$key] = $value;

        $this->scores = json_encode($this->scoredScores);
    }

    public function isComplete()
    {
        return $this->progress >= 100;
    }

    public function getFormattedDateAttribute()
    {
        return ucfirst($this->updated_at->formatLocalized('%B %d, %Y - %H:%I:%S'));
    }

    public function getAnswersContent()
    {
        $questionnaire = $this->questionnaire;
        $questionnaire->loadMissing('pages.questions.answers');
        $givenAnswers = json_decode($this->answers, true);
        $pagesAnswered = [];

        $questions = [];
        foreach($questionnaire->pages as $page) {
            foreach($page->questions as $question) {
                foreach($givenAnswers as $key => $givenAnswer) {
                    if (isset($givenAnswer['question_' . $question->id . '_answer'])) {
                        $foundAnswer = null;

                        foreach($question->answers as $answer) {
                            if ($answer->id == $givenAnswer['question_' . $question->id . '_answer']) {
                                $foundAnswer = $answer->title;
                            }
                        }

                        if ( ! $foundAnswer) {
                            $foundAnswer = $givenAnswer['question_' . $question->id . '_answer'];
                        }

                        $questions[$question->id] = $foundAnswer;

                        $pagesAnswered[$page->id] = true;
                    }
                }

            }
        }

        $content = '';
        foreach($questionnaire->pages as $page) {
            if ($pagesAnswered[$page->id]) {
                $content .= $page->title . PHP_EOL;
                $content .= '---' . PHP_EOL;

                foreach($page->questions as $question) {
                    if (isset($questions[$question->id])) {
                        $content .= $question->title . ': ' . $questions[$question->id] . PHP_EOL;
                    }
                }

                $content .= '---------' . PHP_EOL;
                $content .= PHP_EOL;
            }
        }

        return $content;
    }

}
