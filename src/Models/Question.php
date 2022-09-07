<?php

namespace Questionnaire\Models;

use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Questionnaire\Traits\OptionsTrait;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class Question extends Model implements Sortable
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
        'is_required',
    ];

    protected $casts = [
        'options' => AsCollection::class,
        'order_column' => 'integer',
        'is_active' => 'boolean',
        'is_required' => 'boolean',
    ];

    public function __construct(array $attributes = [])
    {
        $this->setConnection(((env('QUESTIONNAIRE_DATABASE') !== null && env('QUESTIONNAIRE_DATABASE') !== '') ? env('QUESTIONNAIRE_DATABASE') : 'mysql'));

        parent::__construct($attributes);
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Question::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Question::class, 'parent_id');
    }

    public function question_category(): BelongsTo
    {
        return $this->belongsTo(QuestionCategory::class);
    }

    public function question_type(): BelongsTo
    {
        return $this->belongsTo(QuestionType::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class)->ordered();
    }

    public function buildSortQuery()
    {
        return static::query()->where('page_id', $this->page_id);
    }

    public function getAnswer($answerId)
    {
        $answers = $this->answers->keyBy('id');

        return $answers[$answerId];
    }

    public function getPlaceholderAttribute(): mixed
    {
        if ($this->hasOption('placeholder')) {
            return $this->getOption('placeholder');
        }

        if ($this->question_type->hasOption('placeholder')) {
            return $this->question_type->getOption('placeholder');
        }

        return null;
    }

    /**
     * Scope a query to only include active entries
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', '1');
    }

}
