<?php

namespace Questionnaire\Models;

use App\Models\User;
use GregoryDuckworth\Encryptable\EncryptableTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionnaireEntry extends Model
{
    use EncryptableTrait;

    protected $givenAnswers = null;
    protected $scoredScores = null;

    protected $fillable = [
        'name',
        'email',
        'answers',
        'scores',
    ];

    protected $casts = [
//        'answers' => AsCollection::class, // do not use this, the resulting value will always be null, because of the encryption
    ];

    /**
     * Encryptable Rules
     *
     * @var array
     */
    protected $encryptable = [
        'name',
        'email',
        'answers',
    ];

    public function __construct(array $attributes = [])
    {
        $this->setConnection(((env('QUESTIONNAIRE_DATABASE') !== null && env('QUESTIONNAIRE_DATABASE') !== '') ? env('QUESTIONNAIRE_DATABASE') : 'mysql'));

        parent::__construct($attributes);
    }

    public function questionnaire(): BelongsTo
    {
        return $this->belongsTo(Questionnaire::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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

}
