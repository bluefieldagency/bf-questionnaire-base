<?php

namespace Questionnaire\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Questionnaire\Traits\OptionsTrait;

class QuestionnaireEntry extends Model
{

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
        'answers' => AsCollection::class,
        'files' => AsCollection::class,
        'options' => AsCollection::class,
        'name' => 'encrypted',
        'email' => 'encrypted',
        'project_name' => 'encrypted',
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

    public function questionnaire() : BelongsTo
    {
        return $this->belongsTo(Questionnaire::class);
    }

    public function user() : BelongsTo
    {
        return $this->setConnection('mysql')->belongsTo(User::class);
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

    public function questionnaire_invite() : HasOne
    {
        return $this->hasOne(QuestionnaireInvite::class);
    }

}
