<?php

namespace Questionnaire\Models;

use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Questionnaire\Traits\OptionsTrait;

class QuestionnaireInvite extends Model
{
    use HasFactory;
    use OptionsTrait;

    protected $fillable = [
        'name',
        'email',
        'project_name',
        'owner_email',
        'owner_name',
        'hash',
        'is_answered',
        'options',
    ];

    protected $casts = [
        'is_answered' => 'boolean',
        'options' => AsCollection::class,
        'name' => 'encrypted',
        'email' => 'encrypted',
        'project_name' => 'encrypted',
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

    public function questionnaire_entry() : BelongsTo
    {
        return $this->belongsTo(QuestionnaireEntry::class);
    }

}
