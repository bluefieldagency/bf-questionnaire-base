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
        'order_column',
        'is_active',
    ];

    protected $casts = [
        'order_column' => 'integer',
        'is_active' => 'boolean',
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
