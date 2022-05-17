<?php

namespace Bluefield\Questionnaire\Models;

use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class Question extends Model implements Sortable
{

    use HasFactory;
    use SortableTrait;
    use SoftDeletes;

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

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
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
        return $this->hasMany(Answer::class);
    }

    public function buildSortQuery()
    {
        return static::query()->where('page_id', $this->page_id);
    }

}
