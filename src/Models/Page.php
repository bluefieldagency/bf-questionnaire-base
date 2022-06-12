<?php

namespace Questionnaire\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class Page extends Model implements Sortable
{

    use HasFactory;
    use SortableTrait;
    use SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'intro',
        'continue_button_label',
        'custom_view_template',
        'order_column',
        'is_active',
        'show_help_aside',
        'show_questions_numbered',
    ];

    protected $casts = [
        'order_column' => 'integer',
        'is_active' => 'boolean',
        'show_help_aside' => 'boolean',
        'show_questions_numbered' => 'boolean',
    ];

    public function questionnaire(): BelongsTo
    {
        return $this->belongsTo(Questionnaire::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    public function buildSortQuery()
    {
        return static::query()->where('questionnaire_id', $this->questionnaire_id);
    }

}
