<?php

namespace App\Models;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Filters\TaskFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;

class Task extends Model
{
    /** @use HasFactory<\Database\Factories\TaskFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = ['title', 'description', 'status', 'priority', 'due_date', 'assigned_to', 'metadata', 'version'];

    protected $casts = [
        'due_date' => 'date',
        'metadata' => 'array',
        'version' => 'integer',
    ];

    /***
     * One task belongs to  one or mote tags
    */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'tag_task')->withTimestamps();
    }

    /***
     * One task belongs to a single user or admin
    */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /***
     * One task should have multiple log entries
    */
    public function logs(): HasMany
    {
        return $this->hasMany(TaskLog::class);
    }

    /**
     * Filter scope for the all filters
    */
    public function scopeFilter(Builder $query, Request $request): Builder
    {
        return TaskFilter::apply($query, $request);
    }
}
