<?php

namespace Questionnaire\Models;

use GregoryDuckworth\Encryptable\EncryptableTrait;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Questionnaire\Traits\OptionsTrait;

class QuestionnaireInvite extends Model
{
    use HasFactory;
    use EncryptableTrait;
    use OptionsTrait;

    protected $fillable = [
        'name',
        'email',
        'hash',
        'is_answered',
        'options',
    ];

    protected $casts = [
        'is_answered' => 'boolean',
        'options' => AsCollection::class,
    ];

    /**
     * Encryptable Rules
     *
     * @var array
     */
    protected $encryptable = [
        'name',
        'email',
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

}
