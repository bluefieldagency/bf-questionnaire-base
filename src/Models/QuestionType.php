<?php

namespace Questionnaire\Models;

use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Questionnaire\Traits\OptionsTrait;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class QuestionType extends Model implements Sortable
{
    use HasFactory;
    use SortableTrait;
    use SoftDeletes;
    use OptionsTrait;

    protected $fillable = [
        'type',
        'options',
        'order_column',
        'is_unique',
        'is_selectable',
        'is_chartable',
    ];

    protected $casts = [
        'options' => AsCollection::class,
        'order_column' => 'integer',
        'is_unique' => 'boolean',
        'is_selectable' => 'boolean',
    ];

    public function __construct(array $attributes = [])
    {
        $this->setConnection(((env('QUESTIONNAIRE_DATABASE') !== null && env('QUESTIONNAIRE_DATABASE') !== '') ? env('QUESTIONNAIRE_DATABASE') : 'mysql'));

        parent::__construct($attributes);
    }

}
