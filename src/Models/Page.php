<?php

namespace Questionnaire\Models;

use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Questionnaire\Traits\OptionsTrait;
use Questionnaire\Traits\ReplacementsTrait;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class Page extends Model implements Sortable
{

    use HasFactory;
    use SortableTrait;
    use SoftDeletes;
    use OptionsTrait;
    use ReplacementsTrait;

    protected $fillable = [
        'title',
        'slug',
        'intro',
        'continue_button_label',
        'custom_view_template',
        'options',
        'order_column',
        'is_active',
        'show_help_aside',
        'show_questions_numbered',
    ];

    protected $casts = [
        'options' => AsCollection::class,
        'order_column' => 'integer',
        'is_active' => 'boolean',
        'show_help_aside' => 'boolean',
        'show_questions_numbered' => 'boolean',
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

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class)->ordered();
    }

    public function buildSortQuery()
    {
        return static::query()->where('questionnaire_id', $this->questionnaire_id);
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

    public function getTitleAttribute($value)
    {
        return $this->doReplacements($value);
    }

}
