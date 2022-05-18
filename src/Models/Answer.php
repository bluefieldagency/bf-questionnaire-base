<?php

namespace Questionnaire\Models;

use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Questionnaire\Traits\OptionsTrait;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class Answer extends Model implements Sortable
{

    use HasFactory;
    use SortableTrait;
    use SoftDeletes;
    use OptionsTrait;

    protected $fillable = [
        'title',
        'options',
        'order_column',
        'is_active',
    ];

    protected $casts = [
        'options' => AsCollection::class,
        'order_column' => 'integer',
        'is_active' => 'boolean',
    ];

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function buildSortQuery()
    {
        return static::query()->where('question_id', $this->question_id);
    }

}
